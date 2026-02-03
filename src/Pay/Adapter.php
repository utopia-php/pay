<?php

namespace Utopia\Pay;

use Utopia\Pay\Customer\Customer;
use Utopia\Pay\Payment\Payment;
use Utopia\Pay\PaymentMethod\PaymentMethod;
use Utopia\Pay\Refund\Refund;

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
     * @param  int  $amount  Amount to charge in smallest currency unit
     * @param  string  $customerId  Customer ID
     * @param  string|null  $paymentMethodId  Payment method ID (optional)
     * @param  array<string, mixed>  $additionalParams  Additional parameters (optional)
     * @return Payment The payment result
     */
    abstract public function purchase(int $amount, string $customerId, ?string $paymentMethodId = null, array $additionalParams = []): Payment;

    /**
     * Update a payment intent
     *
     * @param  string  $paymentId  Payment intent ID
     * @param  string|null  $paymentMethodId  Payment method ID (optional)
     * @param  int|null  $amount  Amount to update (optional)
     * @param  string|null  $currency  Currency to update (optional)
     * @param  array<string, mixed>  $additionalParams  Additional parameters (optional)
     * @return Payment The updated payment
     */
    abstract public function updatePayment(string $paymentId, ?string $paymentMethodId = null, ?int $amount = null, string $currency = null, array $additionalParams = []): Payment;

    /**
     * Retry a purchase for a payment intent
     *
     * @param  string  $paymentId  The payment intent ID to retry
     * @param  string|null  $paymentMethodId  The payment method to use (optional)
     * @param  array<string, mixed>  $additionalParams  Additional parameters for the retry (optional)
     * @return Payment The result of the retry attempt
     */
    abstract public function retryPurchase(string $paymentId, ?string $paymentMethodId = null, array $additionalParams = []): Payment;

    /**
     * Refund payment
     *
     * @param  string  $paymentId  The payment ID to refund
     * @param  int|null  $amount  Amount to refund (null for full refund)
     * @param  string|null  $reason  Reason for the refund
     * @return Refund The refund result
     */
    abstract public function refund(string $paymentId, int $amount = null, string $reason = null): Refund;

    /**
     * Get a payment details
     *
     * @param  string  $paymentId  The payment ID
     * @return Payment The payment details
     */
    abstract public function getPayment(string $paymentId): Payment;

    /**
     * Add a payment method
     *
     * @param  string  $customerId  Customer ID
     * @param  string  $type  Payment method type
     * @param  array<string, mixed>  $details  Payment method details
     * @return PaymentMethod The created payment method
     */
    abstract public function createPaymentMethod(string $customerId, string $type, array $details): PaymentMethod;

    /**
     * Update payment method billing details
     *
     * @param  string  $paymentMethodId  Payment method ID
     * @param  string|null  $name  Billing name
     * @param  string|null  $email  Billing email
     * @param  string|null  $phone  Billing phone
     * @param  Address|null  $address  Billing address
     * @return PaymentMethod The updated payment method
     */
    abstract public function updatePaymentMethodBillingDetails(string $paymentMethodId, string $name = null, string $email = null, string $phone = null, ?Address $address = null): PaymentMethod;

    /**
     * Update payment method
     *
     * @param  string  $paymentMethodId  Payment method ID
     * @param  string  $type  Payment method type
     * @param  array<string, mixed>  $details  Payment method details
     * @return PaymentMethod The updated payment method
     */
    abstract public function updatePaymentMethod(string $paymentMethodId, string $type, array $details): PaymentMethod;

    /**
     * List payment methods
     *
     * @param  string  $customerId  Customer ID
     * @return array<PaymentMethod> List of payment methods
     */
    abstract public function listPaymentMethods(string $customerId): array;

    /**
     * Remove payment method
     *
     * @param  string  $paymentMethodId  Payment method ID
     * @return bool True if deleted successfully
     */
    abstract public function deletePaymentMethod(string $paymentMethodId): bool;

    /**
     * Add new customer in the gateway database
     *
     * @param  string  $name  Customer name
     * @param  string  $email  Customer email
     * @param  Address|null  $address  Customer address
     * @param  string|null  $paymentMethod  Default payment method ID
     * @return Customer The created customer
     */
    abstract public function createCustomer(string $name, string $email, ?Address $address = null, string $paymentMethod = null): Customer;

    /**
     * List customers
     *
     * @return array<Customer> List of customers
     */
    abstract public function listCustomers(): array;

    /**
     * Get customer details by ID
     *
     * @param  string  $customerId  Customer ID
     * @return Customer The customer details
     */
    abstract public function getCustomer(string $customerId): Customer;

    /**
     * Update customer details
     *
     * @param  string  $customerId  Customer ID
     * @param  string  $name  Customer name
     * @param  string  $email  Customer email
     * @param  Address|null  $address  Customer address
     * @param  string|null  $paymentMethod  Default payment method ID
     * @return Customer The updated customer
     */
    abstract public function updateCustomer(string $customerId, string $name, string $email, Address $address = null, string $paymentMethod = null): Customer;

    /**
     * Delete Customer
     *
     * @param  string  $customerId  Customer ID
     * @return bool True if deleted successfully
     */
    abstract public function deleteCustomer(string $customerId): bool;

    /**
     * Get Payment Method
     *
     * @param  string  $customerId  Customer ID
     * @param  string  $paymentMethodId  Payment method ID
     * @return PaymentMethod The payment method details
     */
    abstract public function getPaymentMethod(string $customerId, string $paymentMethodId): PaymentMethod;

    /**
     * Create setup for accepting future payments
     *
     * @param  string  $customerId  Customer ID
     * @param  string|null  $paymentMethod  Payment method ID
     * @param  array<string>  $paymentMethodTypes  Allowed payment method types
     * @param  array<string, mixed>  $paymentMethodOptions  Payment method options
     * @param  string|null  $paymentMethodConfiguration  Payment method configuration ID
     * @return array<string, mixed> Setup intent data
     */
    abstract public function createFuturePayment(string $customerId, ?string $paymentMethod = null, array $paymentMethodTypes = [], array $paymentMethodOptions = [], ?string $paymentMethodConfiguration = null): array;

    /**
     * List future payments associated with the provided customer or payment method
     *
     * @param  string|null  $customerId  Customer ID
     * @param  string|null  $paymentMethodId  Payment method ID
     * @return array<array<string, mixed>> List of setup intents
     */
    abstract public function listFuturePayments(?string $customerId = null, ?string $paymentMethodId = null): array;

    /**
     * Get Future payment
     *
     * @param  string  $id  Setup intent ID
     * @return array<string, mixed> Setup intent data
     */
    abstract public function getFuturePayment(string $id): array;

    /**
     * Update future payment setup
     *
     * @param  string  $id  Setup intent ID
     * @param  string|null  $customerId  Customer ID
     * @param  string|null  $paymentMethod  Payment method ID
     * @param  array<string, mixed>  $paymentMethodOptions  Payment method options
     * @param  string|null  $paymentMethodConfiguration  Payment method configuration ID
     * @return array<string, mixed> Updated setup intent data
     */
    abstract public function updateFuturePayment(string $id, ?string $customerId = null, ?string $paymentMethod = null, array $paymentMethodOptions = [], ?string $paymentMethodConfiguration = null): array;

    /**
     * Get mandate
     *
     * @param  string  $id  Mandate ID
     * @return array<string, mixed> Mandate data
     */
    abstract public function getMandate(string $id): array;

    /**
     * List disputes
     *
     * @param  int|null  $limit  Maximum number of disputes to return
     * @param  string|null  $paymentIntentId  Filter by payment intent ID
     * @param  string|null  $chargeId  Filter by charge ID
     * @param  int|null  $createdAfter  Filter by creation timestamp
     * @return array<array<string, mixed>> List of disputes
     */
    abstract public function listDisputes(?int $limit = null, ?string $paymentIntentId = null, ?string $chargeId = null, ?int $createdAfter = null): array;

    /**
     * Call
     * Make a request
     *
     * @param  string  $method  HTTP method
     * @param  string  $url  Request URL
     * @param  array<string, mixed>  $params  Request parameters
     * @param  array<string, string>  $headers  Request headers
     * @param  array<int, mixed>  $options  cURL options
     * @return array<string, mixed> Response data
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

    protected function handleError(int $code, mixed $response): void
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
     * @param  array<string, mixed>  $data
     * @param  string  $prefix
     * @return array<string, mixed>
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
