<?php

namespace Utopia\Pay;

abstract class Adapter
{
    protected const METHOD_GET = 'GET';
    protected const METHOD_POST = 'POST';
    protected const METHOD_PUT = 'PUT';
    protected const METHOD_PATCH = 'PATCH';
    protected const METHOD_DELETE = 'DELETE';
    protected const METHOD_HEAD = 'HEAD';
    protected const METHOD_OPTIONS = 'OPTIONS';
    protected const METHOD_CONNECT = 'CONNECT';
    protected const METHOD_TRACE = 'TRACE';

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
    public function setTestMode(bool $testMode) : void
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
    public function setCurrency(string $currency) : void
    {
        $this->currency = $currency;
    }

  /**
   * Get currently set currency for payments
   */
    public function getCurrency() : string
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
    abstract public function updateCard(string $customerId, string $cardId, string $name = null, int $expMonth = null, int $expYear = null, Address $billingAddress = null): array;

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
   * @throws \Exception
   */
    abstract public function createCustomer(string $name, string $email, Address $address = null, string $paymentMethod = null): array;

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
    abstract public function updateCustomer(string $customerId, string $name, string $email, Address $address = null, string $paymentMethod = null): array;

    /**
     * Delete Customer
     *
     * @param string $customerId
     * @return boolean
     */
    abstract public function deleteCustomer(string $customerId): bool;

    /**
     * Call
     * Make a request
     *
     * @param string $method
     * @param string $url
     * @param array $params
     * @param array $headers
     * @param array $options
     * @return array
     */
    protected function call(string $method, string $url, array $params = [], array $headers = [], array $options = []): array
    {

        $responseHeaders = [];
        $ch = \curl_init();

        switch ($headers['content-type']) {
            case 'application/json':
                $query = json_encode($params);
                break;

            case 'multipart/form-data':
                $query = $this->flatten($params);
                break;

            default:
                $query = \http_build_query($params);
                break;
        }


        foreach ($headers as $i => $header) {
            $headers[] = $i . ':' . $header;
            unset($headers[$i]);
        }

        curl_setopt($ch, CURLOPT_HEADEROPT, \CURLHEADER_UNIFIED);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, php_uname('s') . '-' . php_uname('r') . ':php-' . phpversion());
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADERFUNCTION, function ($curl, $header) use (&$responseHeaders) {
            $len = strlen($header);
            $header = explode(':', strtolower($header), 2);

            if (count($header) < 2) { // ignore invalid headers
                return $len;
            }

            $responseHeaders[strtolower(trim($header[0]))] = trim($header[1]);

            return $len;
        });
        if ($method != self::METHOD_GET || $method != self::METHOD_DELETE) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
        }

        foreach ($options as $key => $value) {
            curl_setopt($ch, $key, $value);
        }


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

        if ($responseStatus >= 400) {
            if (is_array($responseBody)) {
                throw new \Exception('Error: ' . json_encode($responseBody), $responseStatus);
            }

            throw new \Exception('Error: ' . $responseBody, $responseStatus);
        }

        curl_close($ch);
        return $responseBody;
    }

  /**
   * Flatten params array to PHP multiple format
   *
   * @param array $data
   * @param string $prefix
   * @return array
   */
    protected function flatten(array $data, $prefix = '')
    {
        $output = [];

        foreach ($data as $key => $value) {
            $finalKey = $prefix ? "{$prefix}[{$key}]" : $key;

            if (is_array($value)) {
                $output += $this->flatten($value, $finalKey); // @todo: handle name collision here if needed
            } else {
                $output[$finalKey] = $value;
            }
        }

        return $output;
    }
}
