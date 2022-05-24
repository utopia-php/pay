<?php

namespace Utopia\Pay;

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
  public function setTestMode(bool $testMode)
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
  public function setCurrency(string $currency)
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
   */
  abstract public function purchase(int $amount, string $customerId, string $cardId, array $additionalParams = []): array;

  /**
   * Refund payment
   */
  abstract public function refund(string $paymentId, int $amount = null): array;

  /**
   * Add a credit card for a customer
   */
  abstract public function createCard(string $customerId, string $cardId): array;

  /**
   * Update credit card
   */
  abstract public function updateCard(string $customerId, string $cardId, string $name = null, int $expMonth = null, int $expYear = null, array $billingDetails = null): array;

  /**
   * Get credit card
   */
  abstract public function getCard(string $customerId, string $cardId): array;

  /**
   * List cards
   */
  abstract public function listCards(string $customerId): array;

  /**
   * Remove a credit card for a customer
   */
  abstract public function deleteCard(string $customerId, string $cardId): bool;

  /**
   * Add new customer in the gateway database
   * returns the id of the newly created customer
   * 
   * @throws Exception
   */
  abstract public function createCustomer(string $name, string $email, array $billingDetails = [], string $paymentMethod = null): array;

  /**
   * List customers
   */
  abstract public function listCustomers(): array;

  /**
   * Get customer details by ID
   */
  abstract public function getCustomer(string $customerId): array;

  /**
   * Update customer details
   */
  abstract public function updateCustomer(string $customerId, string $name, string $email, array $billingDetails = [], string $paymentMethod): array;

  /**
   * Delete customer by ID
   */
  abstract public function deleteCustomer(string $customerId): bool;

  protected function call(string $method, string $url, mixed $body, array $headers = [], array $options = []): mixed
  {

    $responseHeaders = [];
    $ch = \curl_init();

    // define options
    $optArray = array_merge(array(
      CURLOPT_URL => $url,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_CUSTOMREQUEST => $method,
      CURLOPT_POSTFIELDS => $body,
      CURLOPT_HEADEROPT => \CURLHEADER_UNIFIED,
      CURLOPT_HTTPHEADER => $headers,
      CURLOPT_HEADERFUNCTION => function ($curl, $header) use (&$responseHeaders) {
        $len = strlen($header);
        $header = explode(':', strtolower($header), 2);

        if (count($header) < 2) { // ignore invalid headers
          return $len;
        }

        $responseHeaders[strtolower(trim($header[0]))] = trim($header[1]);

        return $len;
      }
    ), $options);

    // apply those options
    \curl_setopt_array($ch, $optArray);


    $responseBody   = curl_exec($ch);
    $responseType   = $responseHeaders['content-type'] ?? '';
    $responseStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    switch ($responseType) {
      case 'application/json':
        $responseBody = json_decode($responseBody, true);
        break;
    }

    if (curl_errno($ch)) {
      throw new \Exception(curl_error($ch), $responseStatus, $responseBody);
    }

    curl_close($ch);
    return ['headers' => $responseHeaders, 'body' => $responseBody];
  }
}
