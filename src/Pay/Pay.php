<?php

namespace Utopia\Pay;

use Utopia\Pay\Customer\Customer;
use Utopia\Pay\Dispute\Dispute;
use Utopia\Pay\Mandate\Mandate;
use Utopia\Pay\Payment\Payment;
use Utopia\Pay\PaymentMethod\PaymentMethod;
use Utopia\Pay\Refund\Refund;
use Utopia\Pay\SetupIntent\SetupIntent;
use Utopia\Pay\Webhook\WebhookEvent;

class Pay
{
    /**
     * @var Adapter
     */
    protected Adapter $adapter;

    /**
     * @param  Adapter  $adapter
     */
    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * Set Test Mode
     *
     * @param  bool  $testMode
     * @return void
     */
    public function setTestMode(bool $testMode): void
    {
        $this->adapter->setTestMode($testMode);
    }

    /**
     * Get Test Mode
     *
     * @return bool
     */
    public function getTestMode(): bool
    {
        return $this->adapter->getTestMode();
    }

    /**
     * Get Name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->adapter->getName();
    }

    /**
     * Set Currency
     *
     * @param  string  $currency
     * @return void
     */
    public function setCurrency(string $currency): void
    {
        $this->adapter->setCurrency($currency);
    }

    /**
     * Get Currency
     * Get currently set currency for payments
     *
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->adapter->getCurrency();
    }

    /**
     * Purchase
     * Make a purchase request
     * Returns payment ID on successfull payment
     *
     * @param  int  $amount
     * @param  string  $customerId
     * @param  string|null  $paymentMethodId
     * @param  array<mixed>  $additionalParams
     * @return Payment
     */
    public function purchase(int $amount, string $customerId, ?string $paymentMethodId = null, array $additionalParams = []): Payment
    {
        return $this->adapter->purchase($amount, $customerId, $paymentMethodId, $additionalParams);
    }

    /**
     * Authorize
     * Authorize a payment (hold funds without capturing)
     * Useful for scenarios where you need to ensure payment availability before providing service
     * Returns authorization ID on successful authorization
     *
     * @param  int  $amount
     * @param  string  $customerId
     * @param  string|null  $paymentMethodId
     * @param  array<mixed>  $additionalParams
     * @return Payment
     */
    public function authorize(int $amount, string $customerId, ?string $paymentMethodId = null, array $additionalParams = []): Payment
    {
        return $this->adapter->authorize($amount, $customerId, $paymentMethodId, $additionalParams);
    }

    /**
     * Capture
     * Capture a previously authorized payment
     * Completes the payment and transfers funds from customer
     *
     * @param  string  $paymentId
     * @param  int|null  $amount
     * @param  array<mixed>  $additionalParams
     * @return Payment
     */
    public function capture(string $paymentId, ?int $amount = null, array $additionalParams = []): Payment
    {
        return $this->adapter->capture($paymentId, $amount, $additionalParams);
    }

    /**
     * Cancel Authorization
     * Cancel/void a payment authorization
     * Releases the hold on funds without capturing
     *
     * @param  string  $paymentId
     * @param  array<mixed>  $additionalParams
     * @return Payment
     */
    public function cancelAuthorization(string $paymentId, array $additionalParams = []): Payment
    {
        return $this->adapter->cancelAuthorization($paymentId, $additionalParams);
    }

    /**
     * Retry a purchase for a payment intent
     *
     * @param  string  $paymentId The payment intent ID to retry
     * @param  string|null  $paymentMethodId The payment method to use (optional)
     * @param  array<mixed>  $additionalParams Additional parameters for the retry (optional)
     * @return Payment The result of the retry attempt
     */
    public function retryPurchase(string $paymentId, ?string $paymentMethodId = null, array $additionalParams = []): Payment
    {
        return $this->adapter->retryPurchase($paymentId, $paymentMethodId, $additionalParams);
    }

    /**
     * Refund Payment
     *
     * @param  string  $paymentId
     * @param  int  $amount
     * @return Refund
     */
    public function refund(string $paymentId, int $amount): Refund
    {
        return $this->adapter->refund($paymentId, $amount);
    }

    /**
     * Get a payment details
     *
     * @param  string  $paymentId
     * @return Payment
     */
    public function getPayment(string $paymentId): Payment
    {
        return $this->adapter->getPayment($paymentId);
    }

    /**
     * Update a payment intent
     *
     * @param  string  $paymentId Payment intent ID
     * @param  string|null  $paymentMethodId Payment method ID (optional)
     * @param  int|null  $amount Amount to update (optional)
     * @param  string|null  $currency Currency to update (optional)
     * @param  array<mixed>  $additionalParams Additional parameters (optional)
     * @return Payment Result of the update
     */
    public function updatePayment(string $paymentId, ?string $paymentMethodId = null, ?int $amount = null, ?string $currency = null, array $additionalParams = []): Payment
    {
        return $this->adapter->updatePayment($paymentId, $paymentMethodId, $amount, $currency, $additionalParams);
    }

    /**
     * Delete Payment Method
     *
     * @param  string  $paymentMethodId
     * @return bool
     */
    public function deletePaymentMethod(string $paymentMethodId): bool
    {
        return $this->adapter->deletePaymentMethod($paymentMethodId);
    }

    /**
     * Create Payment Method
     *
     * @param  string  $customerId
     * @param  string  $type
     * @param  array<mixed>  $details
     * @return PaymentMethod
     */
    public function createPaymentMethod(string $customerId, string $type, array $details): PaymentMethod
    {
        return $this->adapter->createPaymentMethod($customerId, $type, $details);
    }

    /**
     * Update Payment Method Billing Details
     *
     * @param  string  $paymentMethodId
     * @param  string  $type
     * @param  string  $name
     * @param  string  $email
     * @param  string  $phone
     * @param  array<mixed>  $address
     * @return PaymentMethod
     */
    public function updatePaymentMethodBillingDetails(string $paymentMethodId, string $type, ?string $name = null, ?string $email = null, ?string $phone = null, ?array $address = null): PaymentMethod
    {
        return $this->adapter->updatePaymentMethodBillingDetails($paymentMethodId, $name, $email, $phone, $address);
    }

    /**
     * Update Payment Method
     *
     * @param  string  $paymentMethodId
     * @param  string  $type
     * @param  array<mixed>  $details
     * @return PaymentMethod
     */
    public function updatePaymentMethod(string $paymentMethodId, string $type, array $details): PaymentMethod
    {
        return $this->adapter->updatePaymentMethod($paymentMethodId, $type, $details);
    }

    /**
     * Get Payment Method
     *
     * @param  string  $customerId
     * @param  string  $paymentMethodId
     * @return PaymentMethod
     */
    public function getPaymentMethod(string $customerId, string $paymentMethodId): PaymentMethod
    {
        return $this->adapter->getPaymentMethod($customerId, $paymentMethodId);
    }

    /**
     * List Payment Methods
     *
     * @param  string  $customerId
     * @return array<PaymentMethod>
     */
    public function listPaymentMethods(string $customerId): array
    {
        return $this->adapter->listPaymentMethods($customerId);
    }

    /**
     * List Customers
     *
     * @return array<Customer>
     */
    public function listCustomers(): array
    {
        return $this->adapter->listCustomers();
    }

    /**
     * Create Customer
     *
     * Add new customer in the gateway database
     * returns the details of the newly created customer
     *
     * @param  string  $name
     * @param  string  $email
     * @param  array<mixed>  $address
     * @param  string|null  $paymentMethod
     * @return Customer
     */
    public function createCustomer(string $name, string $email, array $address = [], ?string $paymentMethod = null): Customer
    {
        return $this->adapter->createCustomer($name, $email, $address, $paymentMethod);
    }

    /**
     * Get Customer
     *
     * @param  string  $customerId
     * @return Customer
     */
    public function getCustomer(string $customerId): Customer
    {
        return $this->adapter->getCustomer($customerId);
    }

    /**
     * Update Customer
     *
     * @param  string  $customerId
     * @param  string  $name
     * @param  string  $email
     * @param  string  $paymentMethod
     * @param  Address  $address
     * @return Customer
     */
    public function updateCustomer(string $customerId, string $name, string $email, ?Address $address = null, ?string $paymentMethod = null): Customer
    {
        return $this->adapter->updateCustomer($customerId, $name, $email, $address, $paymentMethod);
    }

    /**
     * Delete Customer
     *
     * @param  string  $customerId
     * @return bool
     */
    public function deleteCustomer(string $customerId): bool
    {
        return $this->adapter->deleteCustomer($customerId);
    }

    /**
     * Create Setup for accepting future payments
     *
     * @param  string  $customerId
     * @param  string|null  $paymentMethod
     * @param  array<mixed>  $paymentMethodTypes
     * @param  array<mixed>  $paymentMethodOptions
     * @param  string  $paymentMethodConfiguration
     * @return SetupIntent
     */
    public function createFuturePayment(string $customerId, ?string $paymentMethod = null, array $paymentMethodTypes = ['card'], array $paymentMethodOptions = [], ?string $paymentMethodConfiguration = null): SetupIntent
    {
        return $this->adapter->createFuturePayment($customerId, $paymentMethod, $paymentMethodTypes, $paymentMethodOptions, $paymentMethodConfiguration);
    }

    /**
     * Get future payment
     *
     * @param  string  $id
     * @return SetupIntent
     */
    public function getFuturePayment(string $id): SetupIntent
    {
        return $this->adapter->getFuturePayment($id);
    }

    /**
     * Update Future payment
     *
     * @param  string  $id
     * @param  string|null  $customerId
     * @param  string|null  $paymentMethod
     * @param  array<mixed>  $paymentMethodOptions
     * @param  string|null  $paymentMethodConfiguration
     * @return SetupIntent
     */
    public function updateFuturePayment(string $id, ?string $customerId = null, ?string $paymentMethod = null, array $paymentMethodOptions = [], ?string $paymentMethodConfiguration = null): SetupIntent
    {
        return $this->adapter->updateFuturePayment($id, $customerId, $paymentMethod, $paymentMethodOptions, $paymentMethodConfiguration);
    }

    /**
     * List future payment
     *
     * @param  string|null  $customerId
     * @param  string|null  $paymentMethodId
     * @return array<SetupIntent>
     */
    public function listFuturePayment(?string $customerId, ?string $paymentMethodId = null): array
    {
        return $this->adapter->listFuturePayments($customerId, $paymentMethodId);
    }

    /**
     * Get mandate
     *
     * @param  string  $id
     * @return Mandate
     */
    public function getMandate(string $id): Mandate
    {
        return $this->adapter->getMandate($id);
    }

    /**
     * List disputes
     *
     * @param  int|null  $limit
     * @param  string|null  $paymentIntentId
     * @param  string|null  $chargeId
     * @param  int|null  $createdAfter
     * @return array<Dispute>
     */
    public function listDisputes(?int $limit = null, ?string $paymentIntentId = null, ?string $chargeId = null, ?int $createdAfter = null): array
    {
        return $this->adapter->listDisputes($limit, $paymentIntentId, $chargeId, $createdAfter);
    }

    /**
     * Verify a webhook signature and decode the event
     *
     * @param  string  $payload  Raw request body, exactly as received
     * @param  string  $signatureHeader
     * @param  string  $secret
     * @param  int|null  $tolerance  Maximum age of the signature in seconds, null to skip the check
     * @return WebhookEvent
     *
     * @throws Exception
     */
    public function constructWebhookEvent(string $payload, string $signatureHeader, string $secret, ?int $tolerance = 300): WebhookEvent
    {
        return $this->adapter->constructWebhookEvent($payload, $signatureHeader, $secret, $tolerance);
    }
}
