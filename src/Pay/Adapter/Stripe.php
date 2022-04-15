<?php

namespace Utopia\Pay\Adapter;

use Utopia\Pay\Adapter;

class Stripe extends Adapter {

    private string $baseUrl = 'https://api.stripe.com/v1';
    private string $secretKey;
    private string $publishableKey;


    public function __construct(string $publishableKey, string $secretKey, string $currency = 'USD')
    {
        $this->secretKey = $secretKey;
        $this->publishableKey = $publishableKey;
        $this->currency = $currency;
    }

    /**
     * Get name of the payment gateway
     */
    public function getName() : string {
        return 'Stripe';
    }
  
    /**
     * Make a purchase request
     */
    public function purchase(int $amount, string $customerId, string $cardId = null, array $additonalParams = []) : array {
        $requestBody = [
            'customer' => $customerId,
            'amount' => $amount,
            'currency' => $this->currency,
        ];
        $requestBody = array_merge($requestBody, $additonalParams);
        if(!empty($cardId)) {
            $requestBody['source'] = $cardId;
        }
        $res = $this->execute('POST', '/charges', $requestBody);
        return $res;
    }
  
    /**
     * Refund payment
     */
    public function refund(string $paymentId, int $amount = null) : array {
        $path = '/refunds';
        $requestBody = ['charge' => $paymentId];
        if($amount != null) {
            $requestBody['amount'] = $amount;
        }
        return $this->execute('POST', $path, $requestBody);
    }

    /**
     * Add a credit card for customer
     */
    public function createCard(string $customerId, string $cardId): array {
        $path = '/customers/' . $customerId . '/sources';
        return $this->execute('POST', $path, ['source' => $cardId]);
    }

    /**
     * List cards
     */
    public function listCards(string $customerId): array {
        $path = '/customers/' . $customerId . '/sources';
        return $this->execute('GET', $path);
    }

    /**
     * Update card
     */
    public function updateCard(string $customerId, string $cardId, string $name = null, int $expMonth = null, int $expYear = null, array  $billingDetails = null ): array {
        $path = '/customers/' . $customerId . '/sources/' . $cardId;
        $requestBody = [];
        if(!empty($name)) {
            $requestBody['name'] = $name;
        }
        if(!empty($expMonth)) {
            $requestBody['exp_month'] = $expMonth;
        }
        if(!empty($expYear)) {
            $requestBody['exp_year'] = $expYear;
        }
        return $this->execute('POST', $path, $requestBody);
    }

    /**
     * Get a card
     */
    public function getCard(string $customerId, string $cardId): array {
        $path = '/customers/' . $customerId . '/sources/' . $cardId;
        return $this->execute('GET', $path);
    }

    /**
     * Delete a credit card record
     */
    public function deleteCard(string $customerId, string $cardId) : bool {
        $path = '/customers/' . $customerId . '/sources/' . $cardId;
        $res =  $this->execute('DELETE', $path);
        return $res['deleted'] ?? false;
    }
  
    /**
     * Add new customer in the gateway database
     * returns the id of the newly created customer
     * 
     * @throws Exception
     */
    public function createCustomer(string $name, string $email, array $billingDetails = [], string $paymentMethod = null) : array {
        $path = '/customers';
        $requestBody = [
            'name' => $name,
            'email' => $email,
        ];
        if(!empty($paymentMethod)) {
            $requestBody['payment_method'] = $paymentMethod;
        }
        if(!empty($billingDetails)) {
            $requestBody['billing_details'] = $billingDetails;
        }
        $result = $this->execute('POST', $path, $requestBody);
        return $result;
    }

    /**
     * List customers
     */
    public function listCustomers(): array
    {
        return $this->execute('GET', '/customers');
    }
  
    /**
     * Get customer details by ID
     */
    public function getCustomer(string $customerId) : array {
        $path = '/customers/' . $customerId;
        $result = $this->execute('GET', $path, [], []);
        return $result;
    }
  
    /**
     * Update customer details
     */
    public function updateCustomer(string $customerId, string $name, string $email,  array $billingDetails = [], string $paymentMethod = null) : array {
        $path = '/customers/' . $customerId;
        $requestBody = [
            'name' => $name,
            'email' => $email,
        ];
        if(!empty($paymentMethod)) {
            $requestBody['payment_method'] = $paymentMethod;
        }
        if(!empty($billingDetails)) {
            $requestBody['billing_details'] = $billingDetails;
        }
        return $this->execute('POST', $path, $requestBody);
    }
  
    /**
     * Delete customer by ID
     */
    public function deleteCustomer(string $customerId) : bool {
        $path = '/customers/' . $customerId;
        $result = $this->execute('DELETE', $path);
        return $result['deleted'] ?? false;
    }
    
    private function execute(string $method, string $path, array $requestBody = [], array $headers = ['content-type: application/x-www-form-urlencoded']) {
        $responseHeaders = [];
        $ch = \curl_init();

        // define options
        $optArray = array(
            CURLOPT_URL => $this->baseUrl . $path,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POSTFIELDS => \http_build_query($requestBody),
            CURLOPT_HEADEROPT => \CURLHEADER_UNIFIED,
            CURLOPT_USERPWD => $this->secretKey . ':',
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_HEADERFUNCTION => function($curl, $header) use (&$responseHeaders) {
                $len = strlen($header);
                $header = explode(':', strtolower($header), 2);
    
                if (count($header) < 2) { // ignore invalid headers
                    return $len;
                }
    
                $responseHeaders[strtolower(trim($header[0]))] = trim($header[1]);
    
                return $len;
            }
        );

        // apply those options
        \curl_setopt_array($ch, $optArray);
        

        $responseBody   = curl_exec($ch);
        $responseType   = $responseHeaders['content-type'] ?? '';
        $responseStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        switch($responseType) {
            case 'application/json':
                $responseBody = json_decode($responseBody, true);
            break;
        }

        if (curl_errno($ch)) {
            throw new \Exception(curl_error($ch), $responseStatus, $responseBody);
        }
        
        curl_close($ch);
        return $responseBody;
    }

}