<?php

namespace Utopia\Pay;

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
     * Update a payment intent
     *
     * @param  string  $paymentId Payment intent ID
     * @param  string|null  $paymentMethodId Payment method ID (optional)
     * @param  int|null  $amount Amount to update (optional)
     * @param  string|null  $currency Currency to update (optional)
     * @param  array<mixed>  $additionalParams Additional parameters (optional)
     * @return array<mixed> Result of the update
     */
    abstract public function updatePayment(string $paymentId, ?string $paymentMethodId = null, ?int $amount = null, string $currency = null, array $additionalParams = []): array;

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
    abstract public function refund(string $paymentId, int $amount = null, string $reason = null): array;

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
    abstract public function updatePaymentMethodBillingDetails(string $paymentMethodId, string $name = null, string $email = null, string $phone = null, array $address = null): array;

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
    abstract public function createCustomer(string $name, string $email, array $address = [], string $paymentMethod = null): array;

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
    abstract public function updateCustomer(string $customerId, string $name, string $email, Address $address = null, string $paymentMethod = null): array;

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
        $responseHeaders = [];
        $ch = \curl_init();
        $query = null;

        switch ($headers['content-type'] ?? null) {
            case 'application/json':
                $query = json_encode($params);
                break;

            case 'multipart/form-data':
                $query = $this->flatten($params);
                break;

            default:
                $query = \http_build_query($params);
                break;
        }

        foreach ($headers as $i => $header) {
            $headers[] = $i.':'.$header;
            unset($headers[$i]);
        }

        curl_setopt($ch, CURLOPT_HEADEROPT, \CURLHEADER_UNIFIED);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, php_uname('s').'-'.php_uname('r').':php-'.phpversion());
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADERFUNCTION, function ($curl, $header) use (&$responseHeaders) {
            $len = strlen($header);
            $header = explode(':', strtolower($header), 2);

            if (count($header) < 2) { // ignore invalid headers
                return $len;
            }

            $responseHeaders[strtolower(trim($header[0]))] = trim($header[1]);

            return $len;
        });
        if (! empty($query)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
        }

        foreach ($options as $key => $value) {
            curl_setopt($ch, $key, $value);
        }

        $responseBody = curl_exec($ch);
        $responseType = $responseHeaders['content-type'] ?? '';
        $responseStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        switch ($responseType && is_string($responseBody)) {
            case 'application/json':
                $responseBody = json_decode($responseBody, true);
                break;
        }

        if (curl_errno($ch)) {
            $this->handleError($responseStatus, curl_error($ch));
        }

        if ($responseStatus >= 400) {
            $this->handleError($responseStatus, $responseBody);
        }

        curl_close($ch);

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
