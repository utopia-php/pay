<?php

namespace Utopia\Pay;

class Pay
{
  /**
   * @var Adapter
   */
    protected Adapter $adapter;

    /**
     * @param Adapter $adapter
     */
    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * Set Test Mode
     *
     * @param boolean $testMode
     * @return void
     */
    public function setTestMode(bool $testMode)
    {
        return $this->adapter->setTestMode($testMode);
    }

    /**
     * Get Test Mode
     *
     * @return boolean
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
     * @param string $currency
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
     * @param float $amount
     * @param string $customerId
     * @param string|null $cardId
     * @param array $additionalParams
     * @return array
     */
    public function purchase(float $amount, string $customerId, string $cardId = null, array $additionalParams = []): array
    {
        return $this->adapter->purchase($amount, $customerId, $cardId, $additionalParams);
    }

    /**
     * Refund Payment
     *
     * @param string $paymentId
     * @param float $amount
     * @return array
     */
    public function refund(string $paymentId, float $amount): array
    {
        return $this->adapter->refund($paymentId, $amount);
    }

    /**
     * Cancel Payment
     *
     * @param string $paymentId
     * @return boolean
     */
    public function cancel(string $paymentId): bool
    {
        return $this->adapter->cancel($paymentId);
    }

    /**
     * Delete Card
     *
     * @param string $customerId
     * @param string $cardId
     * @return boolean
     */
    public function deleteCard(string $customerId, string $cardId): bool
    {
        return $this->adapter->deleteCard($customerId, $cardId);
    }

    /**
     * Create Card
     *
     * @param string $customerId
     * @param string $cardId
     * @return array
     */
    public function createCard(string $customerId, string $cardId): array
    {
        return $this->adapter->createCard($customerId, $cardId);
    }

    /**
     * Update Card
     *
     * @param string $customerId
     * @param string $cardId
     * @param string $name
     * @param int $expMonth
     * @param int $expYear
     * @param array $billingDetails
     * @return array
     */
    public function updateCard(string $customerId, string $cardId, string $name = null, int $expMonth = null, int $expYear = null, array $billingDetails = null): array
    {
        return $this->adapter->updateCard($customerId, $cardId, $name, $expMonth, $expYear, $billingDetails);
    }

    /**
     * Get Card
     *
     * @param string $customerId
     * @param string $cardId
     * @return array
     */
    public function getCard(string $customerId, string $cardId): array
    {
        return $this->adapter->getCard($customerId, $cardId);
    }

    /**
     * List Cards
     *
     * @param string $customerId
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
     * @param string $name
     * @param string $email
     * @param array $billingDetails
     * @param string|null $paymentMethod
     * @return array
     */
    public function createCustomer(string $name, string $email, array $billingDetails = [], ?string $paymentMethod = null): array
    {
        return $this->adapter->createCustomer($name, $email, $billingDetails, $paymentMethod);
    }

    /**
     * Get Customer
     *
     * @param string $customerId
     * @return array
     */
    public function getCustomer(string $customerId): array
    {
        return $this->adapter->getCustomer($customerId);
    }

    /**
     * Update Customer
     *
     * @param string $customerId
     * @param string $name
     * @param string $email
     * @param array $billingDetails
     * @param string $paymentMethod
     * @return array
     */
    public function updateCustomer(string $customerId, string $name, string $email, array $billingDetails = [], string $paymentMethod): array
    {
        return $this->adapter->updateCustomer($customerId, $name, $email, $billingDetails, $paymentMethod);
    }

    /**
     * Delete Customer
     *
     * @param string $customerId
     * @return boolean
     */
    public function deleteCustomer(string $customerId): bool
    {
        return $this->adapter->deleteCustomer($customerId);
    }
}
