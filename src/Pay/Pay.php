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
    public function setTestMode(bool $testMode)
    {
        return $this->adapter->setTestMode($testMode);
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
    public function setCurrency(string $currency)
    {
        return $this->adapter->setCurrency($currency);
    }

    /**
     * Get Curreycy
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
     * @param  string|null  $cardId
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
     * Delete Card
     *
     * @param  string  $customerId
     * @param  string  $cardId
     * @return bool
     */
    public function deletePaymentMethod(string $customerId, string $cardId): bool
    {
        return $this->adapter->deletePaymentMethod($customerId, $cardId);
    }

    /**
     * Create Card
     *
     * @param  string  $customerId
     * @param  string  $cardId
     * @return array
     */
    public function createPaymentMethod(string $customerId, string $type, array $paymentMethodDetails): array
    {
        return $this->adapter->createPaymentMethod($customerId, $type, $paymentMethodDetails);
    }

    /**
     * Update Card
     *
     * @param  string  $customerId
     * @param  string  $cardId
     * @param  string  $name
     * @param  int  $expMonth
     * @param  int  $expYear
     * @param  array  $billingDetails
     * @return array
     */
    public function updatePaymentMethodBillingDetails(string $paymentMethodId, string $type, string $name = null, string $email = null, string $phone = null, array $address = null): array
    {
        return $this->adapter->updatePaymentMethodBillingDetails($paymentMethodId, $name, $email, $phone, $address);
    }

    public function updatePaymentMethod(string $paymentMethodId, string $type, array $details): array
    {
        return $this->adapter->updatePaymentMethod($paymentMethodId, $type, $details);
    }

    /**
     * Get Card
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
     * List Cards
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
     * @param  array  $billingDetails
     * @param  string  $paymentMethod
     * @return array
     */
    public function updateCustomer(string $customerId, string $name, string $email, string $paymentMethod, array $address = null): array
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

    public function createFuturePayment(string $customerId, array $paymentMethodTypes = ['card']): array
    {
        return $this->adapter->createFuturePayment($customerId, $paymentMethodTypes);
    }
}
