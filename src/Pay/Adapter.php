<?php

namespace Utopia\Pay;

abstract class Adapter {

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
    public function setTestMode(bool $testMode) {
      $this->testMode = $testMode;
    }
  
    /**
     * Get whether it's in test mode
     */
    public function getTestMode() : bool {
      return $this->testMode;
    } 
  
    /**
     * Get name of the payment gateway
     */
    abstract public function getName() : string;
  
    /**
     * Set the currency for payments
     */
    public function setCurrency(string $currency) {
      $this->currency = $currency;
    }
  
    /**
     * Get currently set currency for payments
     */
    public function getCurrency() : string {
      return $this->currency;
    }
  
    /**
     * Make a purchase request
     */
    abstract public function purchase(float $amount, string $customerId, string $cardId, array $additionalParams = []) : array;
  
    /**
     * Refund payment
     */
    abstract public function refund(string $paymentId, float $amount) : array;
  
    /**
     * Cancel payment
     */
    abstract public function cancel(string $paymentId) : bool;

    /**
     * Add a credit card for a customer
     */

    abstract public function createCard(string $customerId, string $cardId): array;

    /**
     * Update credit card
     */

    abstract public function updateCard(string $customerId, string $cardId, $name = null,  $expMonth = null, $expYear = null, $billingDetails = null): array;

    /**
     * Get credit card
     */
    abstract public function getCard(string $customerId, string $cardId) : array;

    /**
     * List cards
     */
    abstract public function listCards(string $customerId): array;
  
    /**
     * Remove a credit card for a customer
     */
    abstract public function deleteCard(string $customerId, string $cardId) : bool;
  
    /**
     * Add new customer in the gateway database
     * returns the id of the newly created customer
     * 
     * @throws Exception
     */
    abstract public function createCustomer(string $name, string $email, array $billingDetails = [], string $paymentMethod = null) : array;
  
    /**
     * List customers
     */
    abstract public function listCustomers(): array;
    
    /**
     * Get customer details by ID
     */
    abstract public function getCustomer(string $customerId) : array;
  
    /**
     * Update customer details
     */
    abstract public function updateCustomer(string $customerId, string $name, string $email, array $billingDetails = [], string $paymentMethod) : array;
  
    /**
     * Delete customer by ID
     */
    abstract public function deleteCustomer(string $customerId) : bool;
    
  }