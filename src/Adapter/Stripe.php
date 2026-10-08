<?php

declare(strict_types=1);

namespace Utopia\Pay\Adapter;

use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Utopia\Client\Adapter\Curl\Client as Curl;
use Utopia\Client\Client;
use Utopia\Pay\Adapter\Stripe\Currency;
use Utopia\Pay\Address;
use Utopia\Pay\CardDetails;
use Utopia\Pay\CardMandate;
use Utopia\Pay\Customer;
use Utopia\Pay\Dispute;
use Utopia\Pay\Exception;
use Utopia\Pay\Mandate;
use Utopia\Pay\Pay;
use Utopia\Pay\Payload;
use Utopia\Pay\Payment;
use Utopia\Pay\Payment\Options;
use Utopia\Pay\PaymentError;
use Utopia\Pay\PaymentMethod;
use Utopia\Pay\Refund;
use Utopia\Pay\Refund\Reason;
use Utopia\Pay\SetupIntent;
use Utopia\Psr7\Header;
use Utopia\Psr7\Method;
use Utopia\Psr7\Request\Factory as RequestFactory;

/** @phpstan-import-type CardOptions from CardMandate
 * @phpstan-type Params array<string, scalar|null|CardOptions|array<array-key, scalar|null|array<string, scalar|null|list<string>>>> */
class Stripe extends Pay
{
    private const string BASE_URL = 'https://api.stripe.com/v1';

    private readonly ClientInterface $client;

    private readonly RequestFactory $requestFactory;

    /** The default client reuses its connection; inject a client to control transport or pooling. */
    public function __construct(private readonly string $secretKey, private readonly Currency $currency = Currency::USD, ?ClientInterface $client = null)
    {
        $this->client = $client ?? new Client(new Curl())
            ->withConnectionReuse()
            ->withHeaders([
                Header::USER_AGENT => php_uname('s').'-'.php_uname('r').':php-'.phpversion(),
            ]);

        $this->requestFactory = new RequestFactory();
    }

    public function purchase(int $amount, string $customerId, ?string $paymentMethodId = null, ?Options $options = null): Payment
    {
        $path = '/payment_intents';
        $requestBody = [
            'amount' => $amount,
            'currency' => $this->currency->value,
            'customer' => $customerId,
            'payment_method' => $paymentMethodId,
            'off_session' => 'true',
            'confirm' => 'true',
        ];

        $requestBody = $this->filterMandate(array_merge($requestBody, $options?->toArray() ?? []));

        $payload = $this->execute(Method::POST, $path, $requestBody);

        return Payment::fromPayload($payload);
    }

    public function authorize(int $amount, string $customerId, ?string $paymentMethodId = null, ?Options $options = null): Payment
    {
        $path = '/payment_intents';
        $requestBody = [
            'amount' => $amount,
            'currency' => $this->currency->value,
            'customer' => $customerId,
            'payment_method' => $paymentMethodId,
            'capture_method' => 'manual',
            'off_session' => 'true',
            'confirm' => 'true',
        ];

        $requestBody = $this->filterMandate(array_merge($requestBody, $options?->toArray() ?? []));

        $payload = $this->execute(Method::POST, $path, $requestBody);

        return Payment::fromPayload($payload);
    }

    public function capture(string $paymentId, ?int $amount = null, ?Options $options = null): Payment
    {
        $path = '/payment_intents/'.$paymentId.'/capture';
        $requestBody = [];

        if ($amount !== null) {
            $requestBody['amount_to_capture'] = $amount;
        }

        $requestBody = array_merge($requestBody, $options?->toArray() ?? []);
        $payload = $this->execute(Method::POST, $path, $requestBody);

        return Payment::fromPayload($payload);
    }

    public function cancelAuthorization(string $paymentId, ?Options $options = null): Payment
    {
        $path = '/payment_intents/'.$paymentId.'/cancel';
        $payload = $this->execute(Method::POST, $path, $options?->toArray() ?? []);

        return Payment::fromPayload($payload);
    }

    public function retryPurchase(string $paymentId, ?string $paymentMethodId = null, ?Options $options = null): Payment
    {
        $path = '/payment_intents/'.$paymentId.'/confirm';
        $requestBody = [];
        if (! empty($paymentMethodId)) {
            $requestBody = [
                'payment_method' => $paymentMethodId,
            ];
        }

        $requestBody = $this->filterMandate(array_merge($requestBody, $options?->toArray() ?? []), $paymentId);
        $payload = $this->execute(Method::POST, $path, $requestBody);

        return Payment::fromPayload($payload);
    }

    public function refund(string $paymentId, ?int $amount = null, ?Reason $reason = null): Refund
    {
        $path = '/refunds';
        $requestBody = ['payment_intent' => $paymentId];
        if ($amount != null) {
            $requestBody['amount'] = $amount;
        }

        if ($reason != null) {
            $requestBody['reason'] = $reason->value;
        }

        return Refund::fromPayload($this->execute(Method::POST, $path, $requestBody));
    }

    public function getPayment(string $paymentId): Payment
    {
        $path = '/payment_intents/'.$paymentId;

        return Payment::fromPayload($this->execute(Method::GET, $path));
    }

    public function updatePayment(string $paymentId, ?string $paymentMethodId = null, ?int $amount = null, ?string $currency = null, ?Options $options = null): Payment
    {
        $path = '/payment_intents/'.$paymentId;
        $requestBody = [];
        if ($paymentMethodId != null) {
            $requestBody['payment_method'] = $paymentMethodId;
        }
        if ($amount != null) {
            $requestBody['amount'] = $amount;
        }

        if ($currency != null) {
            $requestBody['currency'] = $currency;
        }

        $requestBody = array_merge($requestBody, $options?->toArray() ?? []);

        return Payment::fromPayload($this->execute(Method::POST, $path, $requestBody));
    }

    public function createPaymentMethod(string $customerId, CardDetails $details): PaymentMethod
    {
        $path = '/payment_methods';

        $requestBody = [
            'type' => 'card',
            'card' => $details->toArray(),
        ];

        // Create payment method
        $payload = $this->execute(Method::POST, $path, $requestBody);
        $paymentMethodId = $payload->string('id');
        if ($paymentMethodId === null || $paymentMethodId === '') {
            throw new Exception(message: 'Missing payment method ID', code: 502);
        }

        // attach payment method to the customer
        $path .= '/'.$paymentMethodId.'/attach';

        return PaymentMethod::fromPayload($this->execute(Method::POST, $path, ['customer' => $customerId]));
    }

    /** @return list<PaymentMethod> */
    public function listPaymentMethods(string $customerId): array
    {
        $path = '/customers/'.$customerId.'/payment_methods';

        return array_map(PaymentMethod::fromPayload(...), $this->execute(Method::GET, $path)->objects('data'));
    }

    public function getPaymentMethod(string $customerId, string $paymentMethodId): PaymentMethod
    {
        $path = '/customers/'.$customerId.'/payment_methods/'.$paymentMethodId;

        return PaymentMethod::fromPayload($this->execute(Method::GET, $path));
    }

    public function updatePaymentMethodBillingDetails(string $paymentMethodId, ?string $name = null, ?string $email = null, ?string $phone = null, ?Address $address = null): PaymentMethod
    {
        $path = '/payment_methods/'.$paymentMethodId;
        $requestBody = [];
        $requestBody['billing_details'] = [];
        if (! empty($name)) {
            $requestBody['billing_details']['name'] = $name;
        }
        if (! empty($email)) {
            $requestBody['billing_details']['email'] = $email;
        }
        if (! empty($phone)) {
            $requestBody['billing_details']['phone'] = $phone;
        }
        if (! is_null($address)) {
            $requestBody['billing_details']['address'] = $address->asArray();
        }

        return PaymentMethod::fromPayload($this->execute(Method::POST, $path, $requestBody));
    }

    public function updatePaymentMethod(string $paymentMethodId, CardDetails $details): PaymentMethod
    {
        $path = '/payment_methods/'.$paymentMethodId;

        $requestBody = [
            'card' => $details->toArray(),
        ];

        return PaymentMethod::fromPayload($this->execute(Method::POST, $path, $requestBody));
    }

    public function deletePaymentMethod(string $paymentMethodId): bool
    {
        $path = '/payment_methods/'.$paymentMethodId.'/detach';
        $this->execute(Method::POST, $path);

        return true;
    }

    public function createCustomer(string $name, string $email, ?Address $address = null, ?string $paymentMethod = null): Customer
    {
        $path = '/customers';
        $requestBody = [
            'name' => $name,
            'email' => $email,
        ];
        if (! empty($paymentMethod)) {
            $requestBody['payment_method'] = $paymentMethod;
        }
        if (! empty($address)) {
            $requestBody['address'] = $address->asArray();
        }
        $payload = $this->execute(Method::POST, $path, $requestBody);

        return Customer::fromPayload($payload);
    }

    /** @return list<Customer> */
    public function listCustomers(): array
    {
        return array_map(Customer::fromPayload(...), $this->execute(Method::GET, '/customers')->objects('data'));
    }

    public function getCustomer(string $customerId): Customer
    {
        $path = '/customers/'.$customerId;
        $payload = $this->execute(Method::GET, $path);

        return Customer::fromPayload($payload);
    }

    public function updateCustomer(string $customerId, string $name, string $email, ?Address $address = null, ?string $paymentMethod = null): Customer
    {
        $path = '/customers/'.$customerId;
        $requestBody = [
            'name' => $name,
            'email' => $email,
        ];
        if (! empty($paymentMethod)) {
            $requestBody['payment_method'] = $paymentMethod;
        }
        if (! is_null($address)) {
            $requestBody['address'] = $address->asArray();
        }

        return Customer::fromPayload($this->execute(Method::POST, $path, $requestBody));
    }

    public function deleteCustomer(string $customerId): bool
    {
        $path = '/customers/'.$customerId;
        $payload = $this->execute(Method::DELETE, $path);

        return $payload->boolean('deleted') ?? false;
    }

    /** @param list<string> $paymentMethodTypes */
    public function createFuturePayment(string $customerId, ?string $paymentMethod = null, array $paymentMethodTypes = ['card'], ?CardMandate $mandate = null, ?string $paymentMethodConfiguration = null): SetupIntent
    {
        $path = '/setup_intents';
        $requestBody = [
            'customer' => $customerId,
            'payment_method_types' => $paymentMethodTypes,
        ];

        if ($paymentMethod != null) {
            $requestBody['payment_method'] = $paymentMethod;
        }

        if ($paymentMethodConfiguration != null) {
            $requestBody['payment_method_configuration'] = $paymentMethodConfiguration;
            $requestBody['automatic_payment_methods'] = [
                'enabled' => 'true',
            ];
            unset($requestBody['payment_method_types']);
        }

        if (! empty($mandate)) {
            $requestBody['payment_method_options'] = $mandate->toArray();
        }

        $payload = $this->execute(Method::POST, $path, $requestBody);

        return SetupIntent::fromPayload($payload);
    }

    public function getFuturePayment(string $id): SetupIntent
    {
        $path = '/setup_intents/'.$id;

        return SetupIntent::fromPayload($this->execute(Method::GET, $path));
    }

    /** @return list<SetupIntent> */
    public function listFuturePayment(?string $customerId = null, ?string $paymentMethodId = null): array
    {
        $path = '/setup_intents';
        $requestBody = [];
        if ($customerId != null) {
            $requestBody['customer'] = $customerId;
        }

        if ($paymentMethodId != null) {
            $requestBody['payment_method'] = $paymentMethodId;
        }
        $payload = $this->execute(Method::GET, $path, $requestBody);

        return array_map(SetupIntent::fromPayload(...), $payload->objects('data'));
    }

    public function updateFuturePayment(string $id, ?string $customerId = null, ?string $paymentMethod = null, ?CardMandate $mandate = null, ?string $paymentMethodConfiguration = null): SetupIntent
    {
        $path = '/setup_intents/'.$id;
        $requestBody = [];
        if ($customerId != null) {
            $requestBody['customer'] = $customerId;
        }
        if ($paymentMethod != null) {
            $requestBody['payment_method'] = $paymentMethod;
        }
        if ($paymentMethodConfiguration != null) {
            $requestBody['payment_method_configuration'] = $paymentMethodConfiguration;
        }
        if (! empty($mandate)) {
            $requestBody['payment_method_options'] = $mandate->toArray();
        }

        return SetupIntent::fromPayload($this->execute(Method::POST, $path, $requestBody));
    }

    public function getMandate(string $id): Mandate
    {
        $path = '/mandates/'.$id;

        return Mandate::fromPayload($this->execute(Method::GET, $path));
    }

    /** @return list<Dispute> */
    public function listDisputes(?int $limit = null, ?string $paymentIntentId = null, ?string $chargeId = null, ?int $createdAfter = null): array
    {
        $path = '/disputes';
        $requestBody = [];

        if ($limit !== null) {
            $requestBody['limit'] = $limit;
        }

        if ($paymentIntentId !== null) {
            $requestBody['payment_intent'] = $paymentIntentId;
        }
        if ($chargeId !== null) {
            $requestBody['charge'] = $chargeId;
        }
        if ($createdAfter !== null) {
            $requestBody['created'] = [
                'gte' => $createdAfter,
            ];
        }

        $payload = $this->execute(Method::GET, $path, $requestBody);

        return array_map(Dispute::fromPayload(...), $payload->objects('data'));
    }

    /** A stale or unavailable mandate is omitted so it cannot prevent the charge.
     * @param Params $params
     * @return Params
     */
    private function filterMandate(array $params, ?string $paymentId = null): array
    {
        if (!is_string($params['mandate'] ?? null) || $params['mandate'] === '') {
            unset($params['mandate']);

            return $params;
        }

        try {
            $mandate = $this->getMandate($params['mandate']);
            $methodId = $params['payment_method'] ?? ($paymentId === null ? null : $this->getPayment($paymentId)->paymentMethodId);
            if ($mandate->status === \Utopia\Pay\Mandate\Status::Active && $mandate->paymentMethodId === $methodId && $methodId !== null) {
                return $params;
            }
        } catch (\Throwable) {
            // A failed lookup must not prevent a charge without a mandate.
        }

        unset($params['mandate']);

        return $params;
    }

    /** Stripe uses query filters for GET and form bodies with bracket notation otherwise.
     * @param Params $requestBody */
    private function execute(string $method, string $path, array $requestBody = []): Payload
    {
        $url = self::BASE_URL.$path;

        $request = $method === Method::GET
            ? $this->requestFactory->query($method, $url, $requestBody)
            : $this->requestFactory->form($method, $url, $requestBody);

        $request = $request->withHeader(Header::AUTHORIZATION, 'Bearer '.$this->secretKey);

        try {
            $response = $this->client->sendRequest($request);
        } catch (ClientExceptionInterface $clientException) {
            throw new Exception(message: $clientException->getMessage(), code: 0, previous: $clientException);
        }

        $body = (string) $response->getBody();
        try {
            $decoded = json_decode($body, flags: JSON_THROW_ON_ERROR);
            if (!$decoded instanceof \stdClass) {
                throw new \UnexpectedValueException('Expected a processor JSON object');
            }
            $payload = new Payload($decoded);
            if ($response->getStatusCode() >= 400) {
                $error = $payload->object('error');
                $details = $error === null ? new PaymentError() : PaymentError::fromPayload($error);
                throw new Exception(
                    type: ($details->type === 'card_error' ? $details->declineCode : null) ?? $details->code ?? Exception::GENERAL_UNKNOWN,
                    message: $details->message ?? 'Unknown processor error',
                    code: $response->getStatusCode(),
                    error: $details,
                );
            }
            return $payload;
        } catch (\JsonException|\UnexpectedValueException|\ValueError $e) {
            throw new Exception(message: 'Invalid processor response: '.$e->getMessage(), code: $response->getStatusCode() >= 400 ? $response->getStatusCode() : 502, previous: $e);
        }
    }
}
