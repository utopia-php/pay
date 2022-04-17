<?php

namespace Utopia\Pay;

class Pay
{
  /**
   * @var Adapter
   */
  protected Adapter $adapter;

  public function __construct(Adapter $adapter)
  {
    $this->adapter = $adapter;
  }

  public function setTestMode(bool $testMode)
  {
    return $this->adapter->setTestMode($testMode);
  }

  public function getTestMode(): bool
  {
    return $this->adapter->getTestMode();
  }

  /**
   * Get name of the payment gateway
   */
  public function getName(): string
  {
    return $this->adapter->getName();
  }

  /**
   * Set the currency for payments
   */
  public function setCurrency(string $currency)
  {
    return $this->adapter->setCurrency($currency);
  }

  /**
   * Get currently set currency for payments
   */
  public function getCurrency(): string
  {
    return $this->adapter->getCurrency();
  }

  /**
   * Make a purchase request
   * Returns payment ID on successfull payment
   * 
   */
  public function purchase(float $amount, string $customerId, string $cardId = null, array $additionalParams = []): array
  {
    return $this->adapter->purchase($amount, $customerId, $cardId, $additionalParams);
  }

  /**
   * Refund payment
   */
  public function refund(string $paymentId, float $amount): array
  {
    return $this->adapter->refund($paymentId, $amount);
  }

  /**
   * Cancel payment
   */
  public function cancel(string $paymentId): bool
  {
    return $this->adapter->cancel($paymentId);
  }

  /**
   * Delete a credit card record
   */
  public function deleteCard(string $customerId, string $cardId): bool
  {
    return $this->adapter->deleteCard($customerId, $cardId);
  }


  /**
   * Add a credit card for a customer
   */

  public function createCard(string $customerId, string $cardId): array
  {
    return $this->adapter->createCard($customerId, $cardId);
  }

  /**
   * Update credit card
   */

  public function updateCard(string $customerId, string $cardId, $name = null,  $expMonth = null, $expYear = null, $billingDetails = null): array
  {
    return $this->adapter->updateCard($customerId, $cardId, $name, $expMonth, $expYear, $billingDetails);
  }

  /**
   * Get credit card
   */
  public function getCard(string $customerId, string $cardId): array
  {
    return $this->adapter->getCard($customerId, $cardId);
  }

  /**
   * List cards
   */
  public function listCards(string $customerId): array
  {
    return $this->adapter->listCards($customerId);
  }

  /**
   * List customers
   */
  public function listCustomers(): array 
  { 
    return $this->adapter->listCustomers();
  }

  /**
   * Add new customer in the gateway database
   * returns the id of the newly created customer
   * 
   * $data will contain email, name and payment_method
   * 
   * @throws Exception
   */
  public function createCustomer(string $name, string $email, array $billingDetails = [], string $paymentMethod = null): array
  {
    return $this->adapter->createCustomer($name, $email, $billingDetails, $paymentMethod);
  }

  /**
   * Get customer details by ID
   */
  public function getCustomer(string $customerId): array
  {
    return $this->adapter->getCustomer($customerId);
  }

  /**
   * Update customer details
   */
  public function updateCustomer(string $customerId, string $name, string $email, array $billingDetails = [], string $paymentMethod): array
  {
    return $this->adapter->updateCustomer($customerId, $name, $email, $billingDetails, $paymentMethod);
  }

  /**
   * Delete customer by ID
   */
  public function deleteCustomer(string $customerId): bool
  {
    return $this->adapter->deleteCustomer($customerId);
  }
}
