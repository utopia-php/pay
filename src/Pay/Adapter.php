<?php

namespace Utopia\Pay;

use Utopia\Fetch\Client;
use Utopia\Fetch\Exception as FetchException;

abstract class Adapter
{
    protected const METHOD_GET = 'GET';

    protected const METHOD_POST = 'POST';

    protected const METHOD_PUT = 'PUT';

    protected const METHOD_PATCH = 'PATCH';

    protected const METHOD_DELETE = 'DELETE';

    protected const METHOD_HEAD = 'HEAD';

    protected const METHOD_OPTIONS = 'OPTIONS';

    protected const METHOD_CONNECT = 'CONNECT';

    protected const METHOD_TRACE = 'TRACE';

    /**
     * @var bool
     */
    protected bool $testMode;

    /**
     * @var string
     */
    protected string $currency;

    /**
     * Set test mode
     */
    public function setTestMode(bool $testMode): void
    {
        $this->testMode = $testMode;
    }

    /**
     * Get whether it's in test mode
     */
    public function getTestMode(): bool
    {
        return $this->testMode;
    }

    /**
     * Get name of the payment gateway
     */
    abstract public function getName(): string;

    /**
     * Set the currency for payments
     */
    public function setCurrency(string $currency): void
    {
        $this->currency = $currency;
    }

    /**
     * Get currently set currency for payments
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * Make a purchase request
     *
     * @param  int  $amount Amount to charge
     * @param  string  $customerId Customer ID
     * @param  string|null  $paymentMethodId Payment method ID (optional)
     * @param  array<mixed>  $additionalParams Additional parameters (optional)
     * @return array<mixed> Result of the purchase
     */
    abstract public function purchase(int $amount, string $customerId, ?string $paymentMethodId = null, array $additionalParams = []): array;

    /**
     * Authorize a payment (hold funds without capturing)
     * Useful for scenarios where you need to ensure payment availability before providing service
     *
     * @param  int  $amount Amount to authorize
     * @param  string  $customerId Customer ID
     * @param  string|null  $paymentMethodId Payment method ID (optional)
     * @param  array<mixed>  $additionalParams Additional parameters (optional)
     * @return array<mixed> Result of the authorization including authorization ID
     */
    abstract public function authorize(int $amount, string $customerId, ?string $paymentMethodId = null, array $additionalParams = []): array;

    /**
     * Capture a previously authorized payment
     * Completes the payment and transfers funds from customer
     *
     * @param  string  $paymentId The payment/authorization ID to capture
     * @param  int|null  $amount Amount to capture (optional, defaults to full authorized amount)
     * @param  array<mixed>  $additionalParams Additional parameters (optional)
     * @return array<mixed> Result of the capture
     */
    abstract public function capture(string $paymentId, ?int $amount = null, array $additionalParams = []): array;

    /**
     * Cancel/void a payment authorization
     * Releases the hold on funds without capturing
     *
     * @param  string  $paymentId The payment/authorization ID to cancel
     * @param  array<mixed>  $additionalParams Additional parameters (optional)
     * @return array<mixed> Result of the cancellation
     */
    abstract public function cancelAuthorization(string $paymentId, array $additionalParams = []): array;

    /**
     * Update a payment intent
     *
     * @param  string  $paymentId Payment intent ID
     * @param  string|null  $paymentMethodId Payment method ID (optional)
     * @param  int|null  $amount Amount to update (optional)
     * @param  string|null  $currency Currency to update (optional)
     * @param  array<mixed>  $additionalParams Additional parameters (optional)
     * @return array<mixed> Result of the update
     */
    abstract public function updatePayment(string $paymentId, ?string $paymentMethodId = null, ?int $amount = null, ?string $currency = null, array $additionalParams = []): array;

    /**
     * Retry a purchase for a payment intent
     *
     * @param  string  $paymentId The payment intent ID to retry
     * @param  string|null  $paymentMethodId The payment method to use (optional)
     * @param  array<mixed>  $additionalParams Additional parameters for the retry (optional)
     * @return array<mixed> The result of the retry attempt
     */
    abstract public function retryPurchase(string $paymentId, ?string $paymentMethodId = null, array $additionalParams = []): array;

    /**
     * Refund payment
     *
     * @param  string  $paymentId
     * @param  int  $amount
     * @param  string  $reason
     * @return array<mixed>
     */
    abstract public function refund(string $paymentId, ?int $amount = null, ?string $reason = null): array;

    /**
     * Get a payment details
     *
     * @param  string  $paymentId
     * @return array<mixed>
     */
    abstract public function getPayment(string $paymentId): array;

    /**
     * Add a payment method
     *
     * @param  string  $customerId
     * @param  string  $type
     * @param  array<mixed>  $details
     * @return array<mixed>
     */
    abstract public function createPaymentMethod(string $customerId, string $type, array $details): array;

    /**
     * Update payment method billing details
     *
     * @param  string  $paymentMethodId
     * @param  string|null  $name
     * @param  string|null  $email
     * @param  string|null  $phone
     * @param  array<mixed>|null  $address
     * @return array<mixed>
     */
    abstract public function updatePaymentMethodBillingDetails(string $paymentMethodId, ?string $name = null, ?string $email = null, ?string $phone = null, ?array $address = null): array;

    /**
     * Update payment method
     *
     * @param  string  $paymentMethodId
     * @param  string  $type
     * @param  array<mixed>  $details
     * @return array<mixed>
     */
    abstract public function updatePaymentMethod(string $paymentMethodId, string $type, array $details): array;

    /**
     * List payment methods
     *
     * @param  string  $customerId
     * @return array<mixed>
     */
    abstract public function listPaymentMethods(string $customerId): array;

    /**
     * Remove payment method
     *
     * @param  string  $paymentMethodId
     * @return bool
     */
    abstract public function deletePaymentMethod(string $paymentMethodId): bool;

    /**
     * Add new customer in the gateway database
     *
     * @param  string  $name
     * @param  string  $email
     * @param  array<mixed>  $address
     * @param  string|null  $paymentMethod
     * @return array<mixed>
     */
    abstract public function createCustomer(string $name, string $email, array $address = [], ?string $paymentMethod = null): array;

    /**
     * List customers
     *
     * @return array<mixed>
     */
    abstract public function listCustomers(): array;

    /**
     * Get customer details by ID
     *
     * @param  string  $customerId
     * @return array<mixed>
     */
    abstract public function getCustomer(string $customerId): array;

    /**
     * Update customer details
     *
     * @param  string  $customerId
     * @param  string  $name
     * @param  string  $email
     * @param  Address|null  $address
     * @param  string|null  $paymentMethod
     * @return array<mixed>
     */
    abstract public function updateCustomer(string $customerId, string $name, string $email, ?Address $address = null, ?string $paymentMethod = null): array;

    /**
     * Delete Customer
     *
     * @param  string  $customerId
     * @return bool
     */
    abstract public function deleteCustomer(string $customerId): bool;

    /**
     * List Payment Methods
     *
     * @param  string  $customerId
     * @param  string  $paymentMethodId
     * @return array<mixed>
     */
    abstract public function getPaymentMethod(string $customerId, string $paymentMethodId): array;

    /**
     * Create setup for accepting future payments
     *
     * @param  string  $customerId
     * @param  string|null  $paymentMethod
     * @param  array<mixed>  $paymentMethodTypes
     * @param  array<mixed>  $paymentMethodOptions
     * @param  ?string  $paymentMethodConfiguration
     * @return array<mixed>
     */
    abstract public function createFuturePayment(string $customerId, ?string $paymentMethod = null, array $paymentMethodTypes = [], array $paymentMethodOptions = [], ?string $paymentMethodConfiguration = null): array;

    /**
     * List future payments associated with the provided customer or payment method
     *
     * @param  string|null  $customerId
     * @param  string|null  $paymentMethodId
     * @return array<mixed>
     */
    abstract public function listFuturePayments(?string $customerId = null, ?string $paymentMethodId = null): array;

    /**
     * Get Future payment
     *
     * @param  string  $id
     * @return array<mixed>
     */
    abstract public function getFuturePayment(string $id): array;

    /**
     * Update future payment setup
     *
     * @param  string  $id,
     * @param  string  $customerId
     * @param  string|null  $paymentMethod
     * @param  array<mixed>  $paymentMethodOptions
     * @param  string|null  $paymentMethodConfiguration
     * @return array<mixed>
     */
    abstract public function updateFuturePayment(string $id, ?string $customerId = null, ?string $paymentMethod = null, array $paymentMethodOptions = [], ?string $paymentMethodConfiguration = null): array;

    /**
     * Get mandate
     *
     * @param  string  $id
     * @return array<mixed>
     */
    abstract public function getMandate(string $id): array;

    /**
     * List disputes
     *
     * @param  int|null  $limit
     * @param  string|null  $paymentIntentId
     * @param  string|null  $chargeId
     * @param  int|null  $createdAfter
     * @return array
     */
    abstract public function listDisputes(?int $limit = null, ?string $paymentIntentId = null, ?string $chargeId = null, ?int $createdAfter = null): array;

    /**
     * Call
     * Make a request
     *
     * @param  string  $method
     * @param  string  $url
     * @param  array<mixed>  $params
     * @param  array<mixed>  $headers
     * @param  array<mixed>  $options
     * @return array<mixed>
     */
    protected function call(string $method, string $url, array $params = [], array $headers = [], array $options = []): array
    {
        $query = match ($headers['content-type'] ?? null) {
            'application/json' => json_encode($params),
            'multipart/form-data' => $this->flatten($params),
            default => \http_build_query($params),
        };

        $client = (new Client())
            ->setUserAgent(php_uname('s').'-'.php_uname('r').':php-'.phpversion())
            ->setAllowRedirects(true);

        foreach ($headers as $key => $value) {
            $client->addHeader($key, $value);
        }

        try {
            $response = $client->fetch(
                url: $url,
                method: $method,
                body: empty($query) ? null : $query,
                query: [],
            );
        } catch (FetchException $e) {
            $this->handleError(0, $e->getMessage());
            throw $e;
        }

        $responseHeaders = $response->getHeaders();
        $responseBody = $response->text();
        $responseType = $responseHeaders['content-type'] ?? '';
        $responseStatus = $response->getStatusCode();

        if (! empty($responseType)) {
            $responseBody = json_decode($responseBody, true);
        }

        if ($responseStatus >= 400) {
            $this->handleError($responseStatus, $responseBody);
        }

        return $responseBody;
    }

    protected function handleError(int $code, mixed $response)
    {
        if (is_array($response)) {
            /** @phpstan-ignore-next-line */
            throw new \Exception(json_encode($response), $code);
        }

        throw new \Exception($response, $code);
    }

    /**
     * Flatten params array to PHP multiple format
     *
     * @param  array<mixed>  $data
     * @param  string  $prefix
     * @return array<mixed>
     */
    protected function flatten(array $data, $prefix = ''): array
    {
        $output = [];

        foreach ($data as $key => $value) {
            $finalKey = $prefix ? "{$prefix}[{$key}]" : $key;

            if (is_array($value)) {
                $output += $this->flatten($value, $finalKey); // @todo: handle name collision here if needed
            } else {
                $output[$finalKey] = $value;
            }
        }

        return $output;
    }
}
