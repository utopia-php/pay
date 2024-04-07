<?php

namespace Utopia\Pay;

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
     * @param  array  $additionalParams
     * @return array
     */
    public function purchase(int $amount, string $customerId, string $paymentMethodId = null, array $additionalParams = []): array
    {
        return $this->adapter->purchase($amount, $customerId, $paymentMethodId, $additionalParams);
    }

    /**
     * Refund Payment
     *
     * @param  string  $paymentId
     * @param  int  $amount
     * @return array
     */
    public function refund(string $paymentId, int $amount): array
    {
        return $this->adapter->refund($paymentId, $amount);
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
     * @param  array  $details
     * @return array
     */
    public function createPaymentMethod(string $customerId, string $type, array $details): array
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
     * @param  array  $address
     * @return array
     */
    public function updatePaymentMethodBillingDetails(string $paymentMethodId, string $type, string $name = null, string $email = null, string $phone = null, array $address = null): array
    {
        return $this->adapter->updatePaymentMethodBillingDetails($paymentMethodId, $name, $email, $phone, $address);
    }

    /**
     * Update Payment Method
     *
     * @param  string  $paymentMethodId
     * @param  string  $type
     * @param  array  $details
     * @return array
     */
    public function updatePaymentMethod(string $paymentMethodId, string $type, array $details): array
    {
        return $this->adapter->updatePaymentMethod($paymentMethodId, $type, $details);
    }

    /**
     * Get Payment Method
     *
     * @param  string  $customerId
     * @param  string  $paymentMethodId
     * @return array
     */
    public function getPaymentMethod(string $customerId, string $paymentMethodId): array
    {
        return $this->adapter->getPaymentMethod($customerId, $paymentMethodId);
    }

    /**
     * List Payment Methods
     *
     * @param  string  $customerId
     * @return array
     */
    public function listPaymentMethods(string $customerId): array
    {
        return $this->adapter->listPaymentMethods($customerId);
    }

    /**
     * List Customers
     *
     * @return array
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
     * @param  array  $address
     * @param  string|null  $paymentMethod
     * @return array
     */
    public function createCustomer(string $name, string $email, array $address = [], ?string $paymentMethod = null): array
    {
        return $this->adapter->createCustomer($name, $email, $address, $paymentMethod);
    }

    /**
     * Get Customer
     *
     * @param  string  $customerId
     * @return array
     */
    public function getCustomer(string $customerId): array
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
     * @return array
     */
    public function updateCustomer(string $customerId, string $name, string $email, Address $address = null, ?string $paymentMethod = null): array
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
     * @param  array  $paymentMethodTypes
     * @param  array  $paymentMethodOptions
     * @param  string  $paymentMethodConfiguration
     * @return array
     */
    public function createFuturePayment(string $customerId, ?string $paymentMethod = null, array $paymentMethodTypes = ['card'], array $paymentMethodOptions = [], ?string $paymentMethodConfiguration = null): array
    {
        return $this->adapter->createFuturePayment($customerId, $paymentMethod, $paymentMethodTypes, $paymentMethodOptions, $paymentMethodConfiguration);
    }

    /**
     * Get future payment
     *
     * @param  string  $id
     * @return array
     */
    public function getFuturePayment(string $id): array
    {
        return $this->adapter->getFuturePayment($id);
    }

    /**
     * Update Future payment
     *
     * @param  string  $id
     * @param  string|null  $customerId
     * @param  string|null  $paymentMethod
     * @param  array  $paymentMethodOptions
     * @param  string|null  $paymentMethodConfiguration
     * @return array
     */
    public function updateFuturePayment(string $id, ?string $customerId = null, ?string $paymentMethod = null, array $paymentMethodOptions = [], ?string $paymentMethodConfiguration = null): array
    {
        return $this->adapter->updateFuturePayment($id, $customerId, $paymentMethod, $paymentMethodOptions, $paymentMethodConfiguration);
    }

    /**
     * List future payment
     *
     * @param  string|null  $customerId
     * @param  string|null  $paymentMethodId
     * @return array
     */
    public function listFuturePayment(?string $customerId, ?string $paymentMethodId = null): array
    {
        return $this->adapter->listFuturePayments($customerId, $paymentMethodId);
    }

    /**
     * Get mandate
     *
     * @param  string  $id
     * @return array
     */
    public function getMandate(string $id): array
    {
        return $this->adapter->getMandate($id);
    }
}
