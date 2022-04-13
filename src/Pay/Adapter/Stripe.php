<?php

namespace Utopia\Pay\Adapter;

use Utopia\Pay\Adapter;

class Stripe extends Adapter {

    private string $baseUrl = 'https://api.stripe.com/v1';
    private string $secretKey;
    private string $publishableKey;


    public function __construct(string $secretKey, string $publishableKey)
    {
        $this->secretKey = $secretKey;
        $this->publishableKey = $publishableKey;
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
    public function purchase(float $amount, string $customerId, string $cardId) : string {
        return '';
    }
  
    /**
     * Refund payment
     */
    public function refund(string $paymentId, float $amount) : bool {
        return true;
    }
  
    /**
     * Cancel payment
     */
    public function cancel(string $paymentId) : bool {
        return true;
    }
  
    /**
     * Delete a credit card record
     */
    public function deleteCard(string $cardId) : bool {
        return true;
    }
  
    /**
     * Add new customer in the gateway database
     * returns the id of the newly created customer
     * 
     * @throws Exception
     */
    public function createCustomer(string $name, string $email, string $paymentMethod = 'cc') : array {
        $path = '/customers';
        $requestBody = [
            'name' => $name,
            'email' => $email,
        ];
        $result = $this->execute('POST', $path, $requestBody, array('Content-Type: application/x-www-form-urlencoded'));
        return $result;
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
    public function updateCustomer(string $customerId, string $name, string $email, string $paymentMethod) : bool {
        return true;
    }
  
    /**
     * Delete customer by ID
     */
    public function deleteCustomer(string $customerId) : bool {
        $path = '/customers/' . $customerId;
        $result = $this->execute('DELETE', $path, [], []);
        var_dump($result);
        return $result['deleted'] ?? false;
    }
    
    private function execute(string $method, string $path, array $requestBody, array $headers) {
        $responseHeaders = [];
        $ch = \curl_init();

        // define options
        $optArray = array(
            CURLOPT_URL => $this->baseUrl . $path,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POSTFIELDS => \http_build_query($requestBody),
            CURLOPT_HEADEROPT => \CURLHEADER_UNIFIED,
            CURLOPT_USERPWD => $this->publishableKey . ':',
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