<?php

namespace Utopia\Pay;

use Utopia\Pay\Customer\Customer;
use Utopia\Pay\Payment\Payment;
use Utopia\Pay\PaymentMethod\PaymentMethod;
use Utopia\Pay\Refund\Refund;

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
     * Returns payment on successful payment
     *
     * @param  int  $amount  Amount in smallest currency unit
     * @param  string  $customerId  Customer ID
     * @param  string|null  $paymentMethodId  Payment method ID
     * @param  array<string, mixed>  $additionalParams  Additional parameters
     * @return Payment The payment result
     */
    public function purchase(int $amount, string $customerId, string $paymentMethodId = null, array $additionalParams = []): Payment
    {
        return $this->adapter->purchase($amount, $customerId, $paymentMethodId, $additionalParams);
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
        return $this->adapter->retryPurchase($paymentId, $paymentMethodId, $additionalParams);
    }

    /**
     * Refund Payment
     *
     * @param  string  $paymentId  The payment ID to refund
     * @param  int|null  $amount  Amount to refund (null for full refund)
     * @param  string|null  $reason  Reason for the refund
     * @return Refund The refund result
     */
    public function refund(string $paymentId, ?int $amount = null, ?string $reason = null): Refund
    {
        return $this->adapter->refund($paymentId, $amount, $reason);
    }

    /**
     * Get a payment details
     *
     * @param  string  $paymentId  The payment ID
     * @return Payment The payment details
     */
    public function getPayment(string $paymentId): Payment
    {
        return $this->adapter->getPayment($paymentId);
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
        return $this->adapter->updatePayment($paymentId, $paymentMethodId, $amount, $currency, $additionalParams);
    }

    /**
     * Delete Payment Method
     *
     * @param  string  $paymentMethodId  Payment method ID
     * @return bool True if deleted successfully
     */
    public function deletePaymentMethod(string $paymentMethodId): bool
    {
        return $this->adapter->deletePaymentMethod($paymentMethodId);
    }

    /**
     * Create Payment Method
     *
     * @param  string  $customerId  Customer ID
     * @param  string  $type  Payment method type
     * @param  array<string, mixed>  $details  Payment method details
     * @return PaymentMethod The created payment method
     */
    public function createPaymentMethod(string $customerId, string $type, array $details): PaymentMethod
    {
        return $this->adapter->createPaymentMethod($customerId, $type, $details);
    }

    /**
     * Update Payment Method Billing Details
     *
     * @param  string  $paymentMethodId  Payment method ID
     * @param  string|null  $name  Billing name
     * @param  string|null  $email  Billing email
     * @param  string|null  $phone  Billing phone
     * @param  Address|null  $address  Billing address
     * @return PaymentMethod The updated payment method
     */
    public function updatePaymentMethodBillingDetails(string $paymentMethodId, string $name = null, string $email = null, string $phone = null, ?Address $address = null): PaymentMethod
    {
        return $this->adapter->updatePaymentMethodBillingDetails($paymentMethodId, $name, $email, $phone, $address);
    }

    /**
     * Update Payment Method
     *
     * @param  string  $paymentMethodId  Payment method ID
     * @param  string  $type  Payment method type
     * @param  array<string, mixed>  $details  Payment method details
     * @return PaymentMethod The updated payment method
     */
    public function updatePaymentMethod(string $paymentMethodId, string $type, array $details): PaymentMethod
    {
        return $this->adapter->updatePaymentMethod($paymentMethodId, $type, $details);
    }

    /**
     * Get Payment Method
     *
     * @param  string  $customerId  Customer ID
     * @param  string  $paymentMethodId  Payment method ID
     * @return PaymentMethod The payment method details
     */
    public function getPaymentMethod(string $customerId, string $paymentMethodId): PaymentMethod
    {
        return $this->adapter->getPaymentMethod($customerId, $paymentMethodId);
    }

    /**
     * List Payment Methods
     *
     * @param  string  $customerId  Customer ID
     * @return array<PaymentMethod> List of payment methods
     */
    public function listPaymentMethods(string $customerId): array
    {
        return $this->adapter->listPaymentMethods($customerId);
    }

    /**
     * List Customers
     *
     * @return array<Customer> List of customers
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
     * @param  string  $name  Customer name
     * @param  string  $email  Customer email
     * @param  Address|null  $address  Customer address
     * @param  string|null  $paymentMethod  Default payment method ID
     * @return Customer The created customer
     */
    public function createCustomer(string $name, string $email, ?Address $address = null, ?string $paymentMethod = null): Customer
    {
        return $this->adapter->createCustomer($name, $email, $address, $paymentMethod);
    }

    /**
     * Get Customer
     *
     * @param  string  $customerId  Customer ID
     * @return Customer The customer details
     */
    public function getCustomer(string $customerId): Customer
    {
        return $this->adapter->getCustomer($customerId);
    }

    /**
     * Update Customer
     *
     * @param  string  $customerId  Customer ID
     * @param  string  $name  Customer name
     * @param  string  $email  Customer email
     * @param  Address|null  $address  Customer address
     * @param  string|null  $paymentMethod  Default payment method ID
     * @return Customer The updated customer
     */
    public function updateCustomer(string $customerId, string $name, string $email, Address $address = null, ?string $paymentMethod = null): Customer
    {
        return $this->adapter->updateCustomer($customerId, $name, $email, $address, $paymentMethod);
    }

    /**
     * Delete Customer
     *
     * @param  string  $customerId  Customer ID
     * @return bool True if deleted successfully
     */
    public function deleteCustomer(string $customerId): bool
    {
        return $this->adapter->deleteCustomer($customerId);
    }

    /**
     * Create Setup for accepting future payments
     *
     * @param  string  $customerId  Customer ID
     * @param  string|null  $paymentMethod  Payment method ID
     * @param  array<string>  $paymentMethodTypes  Allowed payment method types
     * @param  array<string, mixed>  $paymentMethodOptions  Payment method options
     * @param  string|null  $paymentMethodConfiguration  Payment method configuration ID
     * @return array<string, mixed> Setup intent data
     */
    public function createFuturePayment(string $customerId, ?string $paymentMethod = null, array $paymentMethodTypes = ['card'], array $paymentMethodOptions = [], ?string $paymentMethodConfiguration = null): array
    {
        return $this->adapter->createFuturePayment($customerId, $paymentMethod, $paymentMethodTypes, $paymentMethodOptions, $paymentMethodConfiguration);
    }

    /**
     * Get future payment
     *
     * @param  string  $id  Setup intent ID
     * @return array<string, mixed> Setup intent data
     */
    public function getFuturePayment(string $id): array
    {
        return $this->adapter->getFuturePayment($id);
    }

    /**
     * Update Future payment
     *
     * @param  string  $id  Setup intent ID
     * @param  string|null  $customerId  Customer ID
     * @param  string|null  $paymentMethod  Payment method ID
     * @param  array<string, mixed>  $paymentMethodOptions  Payment method options
     * @param  string|null  $paymentMethodConfiguration  Payment method configuration ID
     * @return array<string, mixed> Updated setup intent data
     */
    public function updateFuturePayment(string $id, ?string $customerId = null, ?string $paymentMethod = null, array $paymentMethodOptions = [], ?string $paymentMethodConfiguration = null): array
    {
        return $this->adapter->updateFuturePayment($id, $customerId, $paymentMethod, $paymentMethodOptions, $paymentMethodConfiguration);
    }

    /**
     * List future payment
     *
     * @param  string|null  $customerId  Customer ID
     * @param  string|null  $paymentMethodId  Payment method ID
     * @return array<array<string, mixed>> List of setup intents
     */
    public function listFuturePayment(?string $customerId, ?string $paymentMethodId = null): array
    {
        return $this->adapter->listFuturePayments($customerId, $paymentMethodId);
    }

    /**
     * Get mandate
     *
     * @param  string  $id  Mandate ID
     * @return array<string, mixed> Mandate data
     */
    public function getMandate(string $id): array
    {
        return $this->adapter->getMandate($id);
    }

    /**
     * List disputes
     *
     * @param  int|null  $limit  Maximum number of disputes to return
     * @param  string|null  $paymentIntentId  Filter by payment intent ID
     * @param  string|null  $chargeId  Filter by charge ID
     * @param  int|null  $createdAfter  Filter by creation timestamp
     * @return array<array<string, mixed>> List of disputes
     */
    public function listDisputes(?int $limit = null, ?string $paymentIntentId = null, ?string $chargeId = null, ?int $createdAfter = null): array
    {
        return $this->adapter->listDisputes($limit, $paymentIntentId, $chargeId, $createdAfter);
    }
}
