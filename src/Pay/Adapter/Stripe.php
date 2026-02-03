<?php

namespace Utopia\Pay\Adapter;

use Utopia\Pay\Adapter;
use Utopia\Pay\Address;
use Utopia\Pay\Customer\Customer;
use Utopia\Pay\Exception;
use Utopia\Pay\Payment\Payment;
use Utopia\Pay\PaymentMethod\PaymentMethod;
use Utopia\Pay\Refund\Refund;

class Stripe extends Adapter
{
    private string $baseUrl = 'https://api.stripe.com/v1';

    private string $secretKey;

    public function __construct(string $secretKey, string $currency = 'USD')
    {
        $this->secretKey = $secretKey;
        $this->currency = $currency;
    }

    /**
     * Get name of the payment gateway
     */
    public function getName(): string
    {
        return 'Stripe';
    }

    /**
     * Make a purchase request
     */
    public function purchase(int $amount, string $customerId, ?string $paymentMethodId = null, array $additionalParams = []): Payment
    {
        $path = '/payment_intents';
        $requestBody = [
            'amount' => $amount,
            'currency' => $this->currency,
            'customer' => $customerId,
            'payment_method' => $paymentMethodId,
            'off_session' => 'true',
            'confirm' => 'true',
        ];

        // Extract idempotency key if provided
        $headers = [];
        if (isset($additionalParams[parent::PARAM_IDEMPOTENCY_KEY])) {
            $headers['Idempotency-Key'] = (string) $additionalParams[parent::PARAM_IDEMPOTENCY_KEY];
            unset($additionalParams[parent::PARAM_IDEMPOTENCY_KEY]);
        }

        $requestBody = array_merge($requestBody, $additionalParams);
        $result = $this->execute(self::METHOD_POST, $path, $requestBody, $headers);

        return Payment::fromArray($result);
    }

    /**
     * Retry a purchase for a payment intent
     *
     * @param  string  $paymentId  The payment intent ID to retry
     * @param  string|null  $paymentMethodId  The payment method to use (optional)
     * @param  array<string, mixed>  $additionalParams  Additional parameters for the retry (optional)
     * @return Payment The result of the retry attempt
     */
    public function retryPurchase(string $paymentId, ?string $paymentMethodId = null, array $additionalParams = []): Payment
    {
        $path = '/payment_intents/'.$paymentId.'/confirm';
        $requestBody = [];
        if (! empty($paymentMethodId)) {
            $requestBody = [
                'payment_method' => $paymentMethodId,
            ];
        }

        $requestBody = array_merge($requestBody, $additionalParams);
        $result = $this->execute(self::METHOD_POST, $path, $requestBody);

        return Payment::fromArray($result);
    }

    /**
     * Refund payment
     */
    public function refund(string $paymentId, int $amount = null, string $reason = null, array $additionalParams = []): Refund
    {
        $path = '/refunds';
        $requestBody = ['payment_intent' => $paymentId];
        if ($amount != null) {
            $requestBody['amount'] = $amount;
        }

        if ($reason != null) {
            $requestBody['reason'] = $reason;
        }

        // Extract idempotency key if provided
        $headers = [];
        if (isset($additionalParams[parent::PARAM_IDEMPOTENCY_KEY])) {
            $headers['Idempotency-Key'] = (string) $additionalParams[parent::PARAM_IDEMPOTENCY_KEY];
            unset($additionalParams[parent::PARAM_IDEMPOTENCY_KEY]);
        }

        $requestBody = array_merge($requestBody, $additionalParams);
        $result = $this->execute(self::METHOD_POST, $path, $requestBody, $headers);

        return Refund::fromArray($result);
    }

    /**
     * Get a payment details
     *
     * @param  string  $paymentId
     * @return Payment
     */
    public function getPayment(string $paymentId): Payment
    {
        $path = '/payment_intents/'.$paymentId;
        $result = $this->execute(self::METHOD_GET, $path);

        return Payment::fromArray($result);
    }

    /**
     * Update a payment intent
     *
     * @param  string  $paymentId  Payment intent ID
     * @param  string|null  $paymentMethodId  Payment method ID (optional)
     * @param  int|null  $amount  Amount to update (optional)
     * @param  string|null  $currency  Currency to update (optional)
     * @param  array<string, mixed>  $additionalParams  Additional parameters (optional)
     * @return Payment Result of the update
     */
    public function updatePayment(string $paymentId, ?string $paymentMethodId = null, ?int $amount = null, string $currency = null, array $additionalParams = []): Payment
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

        $requestBody = array_merge($requestBody, $additionalParams);
        $result = $this->execute(self::METHOD_POST, $path, $requestBody);

        return Payment::fromArray($result);
    }

    /**
     * Add a credit card for customer
     */
    public function createPaymentMethod(string $customerId, string $type, array $paymentMethodDetails): PaymentMethod
    {
        $path = '/payment_methods';

        $requestBody = [
            'type' => $type,
            $type => $paymentMethodDetails,
        ];

        // Create payment method
        $paymentMethod = $this->execute(self::METHOD_POST, $path, $requestBody);
        $paymentMethodId = $paymentMethod['id'];

        // attach payment method to the customer
        $path .= '/'.$paymentMethodId.'/attach';

        $result = $this->execute(self::METHOD_POST, $path, ['customer' => $customerId]);

        return PaymentMethod::fromArray($result);
    }

    /**
     * List cards
     *
     * @return array<PaymentMethod>
     */
    public function listPaymentMethods(string $customerId): array
    {
        $path = '/customers/'.$customerId.'/payment_methods';
        $result = $this->execute(self::METHOD_GET, $path);

        $paymentMethods = [];
        foreach ($result['data'] ?? [] as $pm) {
            $paymentMethods[] = PaymentMethod::fromArray($pm);
        }

        return $paymentMethods;
    }

    /**
     * List Customer Payment Methods
     */
    public function getPaymentMethod(string $customerId, string $paymentMethodId): PaymentMethod
    {
        $path = '/customers/'.$customerId.'/payment_methods/'.$paymentMethodId;
        $result = $this->execute(self::METHOD_GET, $path);

        return PaymentMethod::fromArray($result);
    }

    /**
     * Update billing details
     *
     * @param  string  $paymentMethodId
     * @param  string|null  $name
     * @param  string|null  $email
     * @param  string|null  $phone
     * @param  Address|null  $address
     * @return PaymentMethod
     */
    public function updatePaymentMethodBillingDetails(string $paymentMethodId, string $name = null, string $email = null, string $phone = null, ?Address $address = null): PaymentMethod
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

        $result = $this->execute(self::METHOD_POST, $path, $requestBody);

        return PaymentMethod::fromArray($result);
    }

    public function updatePaymentMethod(string $paymentMethodId, string $type, array $details): PaymentMethod
    {
        $path = '/payment_methods/'.$paymentMethodId;

        $requestBody = [
            $type => $details,
        ];

        $result = $this->execute(self::METHOD_POST, $path, $requestBody);

        return PaymentMethod::fromArray($result);
    }

    /**
     * Delete a credit card record
     */
    public function deletePaymentMethod(string $paymentMethodId): bool
    {
        $path = '/payment_methods/'.$paymentMethodId.'/detach';
        $this->execute(self::METHOD_POST, $path);

        return true;
    }

    /**
     * Add new customer in the gateway database
     * returns the newly created customer
     *
     * @throws \Exception
     */
    public function createCustomer(string $name, string $email, ?Address $address = null, string $paymentMethod = null): Customer
    {
        $path = '/customers';
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
        $result = $this->execute(self::METHOD_POST, $path, $requestBody);

        return Customer::fromArray($result);
    }

    /**
     * List customers
     *
     * @return array<Customer>
     */
    public function listCustomers(): array
    {
        $result = $this->execute(self::METHOD_GET, '/customers');

        $customers = [];
        foreach ($result['data'] ?? [] as $customer) {
            $customers[] = Customer::fromArray($customer);
        }

        return $customers;
    }

    /**
     * Get customer details by ID
     */
    public function getCustomer(string $customerId): Customer
    {
        $path = '/customers/'.$customerId;
        $result = $this->execute(self::METHOD_GET, $path);

        return Customer::fromArray($result);
    }

    /**
     * Update customer details
     */
    public function updateCustomer(string $customerId, string $name, string $email, Address $address = null, string $paymentMethod = null): Customer
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

        $result = $this->execute(self::METHOD_POST, $path, $requestBody);

        return Customer::fromArray($result);
    }

    /**
     * Delete customer by ID
     */
    public function deleteCustomer(string $customerId): bool
    {
        $path = '/customers/'.$customerId;
        $result = $this->execute(self::METHOD_DELETE, $path);

        return $result['deleted'] ?? false;
    }

    public function createFuturePayment(string $customerId, ?string $paymentMethod = null, array $paymentMethodTypes = ['card'], array $paymentMethodOptions = [], ?string $paymentMethodConfiguration = null): array
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

        if (! empty($paymentMethodOptions)) {
            $requestBody['payment_method_options'] = $paymentMethodOptions;
        }

        $result = $this->execute(self::METHOD_POST, $path, $requestBody);

        return $result;
    }

    public function getFuturePayment(string $id): array
    {
        $path = '/setup_intents/'.$id;

        return $this->execute(self::METHOD_GET, $path);
    }

    public function listFuturePayments(?string $customerId = null, ?string $pyamentMethodId = null): array
    {
        $path = '/setup_intents';
        $requestBody = [];
        if ($customerId != null) {
            $requestBody['customer'] = $customerId;
        }

        if ($pyamentMethodId != null) {
            $requestBody['payment_method'] = $pyamentMethodId;
        }
        $result = $this->execute(self::METHOD_GET, $path, $requestBody);

        return $result['data'];
    }

    public function updateFuturePayment(string $id, ?string $customerId = null, ?string $paymentMethod = null, array $paymentMethodOptions = [], ?string $paymentMethodConfiguration = null): array
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
        if (! empty($paymentMethodOptions)) {
            $requestBody['payment_method_options'] = $paymentMethodOptions;
        }

        return $this->execute(self::METHOD_POST, $path, $requestBody);
    }

    /**
     * Get mandate
     *
     * @param  string  $id
     * @return array<string, mixed>
     */
    public function getMandate(string $id): array
    {
        $path = '/mandates/'.$id;

        return $this->execute(self::METHOD_GET, $path);
    }

    /**
     * List disputes
     *
     * @param  int|null  $limit
     * @param  string|null  $paymentIntentId
     * @param  string|null  $chargeId
     * @param  int|null  $createdAfter
     * @return array<array<string, mixed>>
     */
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

        $result = $this->execute(self::METHOD_GET, $path, $requestBody);

        return $result['data'];
    }

    /**
     * Execute
     *
     * @param  string  $method
     * @param  string  $path
     * @param  array<string, mixed>  $requestBody
     * @param  array<string, string>  $headers
     * @return array<string, mixed>
     */
    private function execute(string $method, string $path, array $requestBody = [], array $headers = []): array
    {
        $defaultHeaders = ['Authorization' => 'Bearer '.$this->secretKey];

        if ($method !== self::METHOD_GET) {
            $defaultHeaders['content-type'] = 'application/x-www-form-urlencoded';
        }
        $headers = array_merge($defaultHeaders, $headers);

        return $this->call($method, $this->baseUrl.$path, $requestBody, $headers);
    }

    protected function handleError(int $code, mixed $response): void
    {
        if (is_array($response)) {
            // stripe error is inside `error`
            $error = $response['error'] ?? [];
            $type = $error['code'] ?? Exception::GENERAL_UNKNOWN;
            $stripeType = $error['type'] ?? '';
            if ($stripeType == 'card_error') {
                $type = $error['decline_code'] ?? $type;
            }
            $message = $error['message'] ?? 'Unknown error';
            throw new Exception($type, $message, $code, $error);
        }

        // Handle string or null responses
        $message = is_string($response) ? $response : 'Unknown error';
        throw new Exception(Exception::GENERAL_UNKNOWN, $message, $code);
    }
}
