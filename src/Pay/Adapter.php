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

abstract class Adapter
{
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
     * @return Payment Result of the purchase
     */
    abstract public function purchase(int $amount, string $customerId, ?string $paymentMethodId = null, array $additionalParams = []): Payment;

    /**
     * Authorize a payment (hold funds without capturing)
     * Useful for scenarios where you need to ensure payment availability before providing service
     *
     * @param  int  $amount Amount to authorize
     * @param  string  $customerId Customer ID
     * @param  string|null  $paymentMethodId Payment method ID (optional)
     * @param  array<mixed>  $additionalParams Additional parameters (optional)
     * @return Payment Result of the authorization including authorization ID
     */
    abstract public function authorize(int $amount, string $customerId, ?string $paymentMethodId = null, array $additionalParams = []): Payment;

    /**
     * Capture a previously authorized payment
     * Completes the payment and transfers funds from customer
     *
     * @param  string  $paymentId The payment/authorization ID to capture
     * @param  int|null  $amount Amount to capture (optional, defaults to full authorized amount)
     * @param  array<mixed>  $additionalParams Additional parameters (optional)
     * @return Payment Result of the capture
     */
    abstract public function capture(string $paymentId, ?int $amount = null, array $additionalParams = []): Payment;

    /**
     * Cancel/void a payment authorization
     * Releases the hold on funds without capturing
     *
     * @param  string  $paymentId The payment/authorization ID to cancel
     * @param  array<mixed>  $additionalParams Additional parameters (optional)
     * @return Payment Result of the cancellation
     */
    abstract public function cancelAuthorization(string $paymentId, array $additionalParams = []): Payment;

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
    abstract public function updatePayment(string $paymentId, ?string $paymentMethodId = null, ?int $amount = null, ?string $currency = null, array $additionalParams = []): Payment;

    /**
     * Retry a purchase for a payment intent
     *
     * @param  string  $paymentId The payment intent ID to retry
     * @param  string|null  $paymentMethodId The payment method to use (optional)
     * @param  array<mixed>  $additionalParams Additional parameters for the retry (optional)
     * @return Payment The result of the retry attempt
     */
    abstract public function retryPurchase(string $paymentId, ?string $paymentMethodId = null, array $additionalParams = []): Payment;

    /**
     * Refund payment
     *
     * @param  string  $paymentId
     * @param  int  $amount
     * @param  string  $reason
     * @return Refund
     */
    abstract public function refund(string $paymentId, ?int $amount = null, ?string $reason = null): Refund;

    /**
     * Get a payment details
     *
     * @param  string  $paymentId
     * @return Payment
     */
    abstract public function getPayment(string $paymentId): Payment;

    /**
     * Add a payment method
     *
     * @param  string  $customerId
     * @param  string  $type
     * @param  array<mixed>  $details
     * @return PaymentMethod
     */
    abstract public function createPaymentMethod(string $customerId, string $type, array $details): PaymentMethod;

    /**
     * Update payment method billing details
     *
     * @param  string  $paymentMethodId
     * @param  string|null  $name
     * @param  string|null  $email
     * @param  string|null  $phone
     * @param  array<mixed>|null  $address
     * @return PaymentMethod
     */
    abstract public function updatePaymentMethodBillingDetails(string $paymentMethodId, ?string $name = null, ?string $email = null, ?string $phone = null, ?array $address = null): PaymentMethod;

    /**
     * Update payment method
     *
     * @param  string  $paymentMethodId
     * @param  string  $type
     * @param  array<mixed>  $details
     * @return PaymentMethod
     */
    abstract public function updatePaymentMethod(string $paymentMethodId, string $type, array $details): PaymentMethod;

    /**
     * List payment methods
     *
     * @param  string  $customerId
     * @return array<PaymentMethod>
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
     * @return Customer
     */
    abstract public function createCustomer(string $name, string $email, array $address = [], ?string $paymentMethod = null): Customer;

    /**
     * List customers
     *
     * @return array<Customer>
     */
    abstract public function listCustomers(): array;

    /**
     * Get customer details by ID
     *
     * @param  string  $customerId
     * @return Customer
     */
    abstract public function getCustomer(string $customerId): Customer;

    /**
     * Update customer details
     *
     * @param  string  $customerId
     * @param  string  $name
     * @param  string  $email
     * @param  Address|null  $address
     * @param  string|null  $paymentMethod
     * @return Customer
     */
    abstract public function updateCustomer(string $customerId, string $name, string $email, ?Address $address = null, ?string $paymentMethod = null): Customer;

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
     * @return PaymentMethod
     */
    abstract public function getPaymentMethod(string $customerId, string $paymentMethodId): PaymentMethod;

    /**
     * Create setup for accepting future payments
     *
     * @param  string  $customerId
     * @param  string|null  $paymentMethod
     * @param  array<mixed>  $paymentMethodTypes
     * @param  array<mixed>  $paymentMethodOptions
     * @param  ?string  $paymentMethodConfiguration
     * @return SetupIntent
     */
    abstract public function createFuturePayment(string $customerId, ?string $paymentMethod = null, array $paymentMethodTypes = [], array $paymentMethodOptions = [], ?string $paymentMethodConfiguration = null): SetupIntent;

    /**
     * List future payments associated with the provided customer or payment method
     *
     * @param  string|null  $customerId
     * @param  string|null  $paymentMethodId
     * @return array<SetupIntent>
     */
    abstract public function listFuturePayments(?string $customerId = null, ?string $paymentMethodId = null): array;

    /**
     * Get Future payment
     *
     * @param  string  $id
     * @return SetupIntent
     */
    abstract public function getFuturePayment(string $id): SetupIntent;

    /**
     * Update future payment setup
     *
     * @param  string  $id,
     * @param  string  $customerId
     * @param  string|null  $paymentMethod
     * @param  array<mixed>  $paymentMethodOptions
     * @param  string|null  $paymentMethodConfiguration
     * @return SetupIntent
     */
    abstract public function updateFuturePayment(string $id, ?string $customerId = null, ?string $paymentMethod = null, array $paymentMethodOptions = [], ?string $paymentMethodConfiguration = null): SetupIntent;

    /**
     * Get mandate
     *
     * @param  string  $id
     * @return Mandate
     */
    abstract public function getMandate(string $id): Mandate;

    /**
     * List disputes
     *
     * @param  int|null  $limit
     * @param  string|null  $paymentIntentId
     * @param  string|null  $chargeId
     * @param  int|null  $createdAfter
     * @return array<Dispute>
     */
    abstract public function listDisputes(?int $limit = null, ?string $paymentIntentId = null, ?string $chargeId = null, ?int $createdAfter = null): array;

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
        // Not abstract so adding it does not break third-party adapters
        throw new Exception(Exception::GENERAL_UNKNOWN, $this->getName().' does not support webhooks');
    }
}
