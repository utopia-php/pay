<?php

namespace Utopia\Pay;

abstract class Adapter {

    /**
     * @var bool
     */
    protected bool $testMode;
  
    /**
     * Set test mode
     */
    abstract public function setTestMode(bool $testMode);
  
    /**
     * Get whether it's in test mode
     */
    abstract public function getTestMode() : bool; 
  
    /**
     * Get name of the payment gateway
     */
    abstract public function getName() : string;
  
    /**
     * Set the currency for payments
     */
    abstract public function setCurrency(string $currency) : bool;
  
    /**
     * Get currently set currency for payments
     */
    abstract public function getCurrency() : string;
  
    /**
     * Make a purchase request
     */
    abstract public function purchase(float $amount, string $customerId, string $cardId) : string;
  
    /**
     * Refund payment
     */
    abstract public function refund(string $paymentId, float $amount) : bool;
  
    /**
     * Cancel payment
     */
    abstract public function cancel(string $paymentId) : bool;
  
    /**
     * Delete a credit card record
     */
    abstract public function deleteCard(string $cardId) : bool;
  
    // thinking may be managing customer and billing address also handle this inside Appwrite if possible. For Stripe it seems if we manage customers ourselves, we will have to get the creditCard record for each purchase request, we cannot just pass the reference to credit card
  
    /**
     * Add new customer in the gateway database
     * returns the id of the newly created customer
     * 
     * @throws Exception
     */
    abstract public function createCustomer(string $name, string $email, string $paymentMethod = 'cc') : string;
  
    /**
     * Get customer details by ID
     */
    abstract public function getCustomer(string $customerId) : array;
  
    /**
     * Update customer details
     */
    abstract public function updateCustomer(string $customerId, string $name, string $email, string $paymentMethod) : bool;
  
    /**
     * Delete customer by ID
     */
    abstract public function deleteCustomer(string $customerId) : bool;
    
  }