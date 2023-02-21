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
     * @param  float  $amount
     * @param  string  $customerId
     * @param  string|null  $cardId
     * @param  array  $additionalParams
     * @return array
     */
    public function purchase(float $amount, string $customerId, string $cardId = null, array $additionalParams = []): array
    {
        return $this->adapter->purchase($amount, $customerId, $cardId, $additionalParams);
    }

    /**
     * Refund Payment
     *
     * @param  string  $paymentId
     * @param  float  $amount
     * @return array
     */
    public function refund(string $paymentId, float $amount): array
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
    public function deleteCard(string $customerId, string $cardId): bool
    {
        return $this->adapter->deleteCard($customerId, $cardId);
    }

    /**
     * Create Card
     *
     * @param  string  $customerId
     * @param  string  $cardId
     * @return array
     */
    public function createCard(string $customerId, string $cardId): array
    {
        return $this->adapter->createCard($customerId, $cardId);
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
    public function updateCard(string $customerId, string $cardId, string $name = null, int $expMonth = null, int $expYear = null, array $billingDetails = null): array
    {
        return $this->adapter->updateCard($customerId, $cardId, $name, $expMonth, $expYear, $billingDetails);
    }

    /**
     * Get Card
     *
     * @param  string  $customerId
     * @param  string  $cardId
     * @return array
     */
    public function getCard(string $customerId, string $cardId): array
    {
        return $this->adapter->getCard($customerId, $cardId);
    }

    /**
     * List Cards
     *
     * @param  string  $customerId
     * @return array
     */
    public function listCards(string $customerId): array
    {
        return $this->adapter->listCards($customerId);
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

    /**
     * List Customer Payment Methods
     */
    public function listCustomerPaymentMethods(string $customerId): array
    {
        return $this->adapter->listCustomerPaymentMethods($customerId);
    }

    /**
     * List Customer Payment Methods
     */
    public function getCustomerPaymentMethod(string $customerId, string $paymentMethodId): array
    {
        return $this->adapter->getCustomerPaymentMethod($customerId, $paymentMethodId);
    }

    public function createPaymentIntent(string $customerId, string $paymentMethodId, int $amount): array
    {
        return $this->adapter->createPaymentIntent($customerId, $paymentMethodId, $amount);
    }

    public function createFuturePayment(string $customerId, array $paymentMethodTypes = ['card']): array
    {
        return $this->adapter->createFuturePayment($customerId, $paymentMethodTypes);
    }
}
