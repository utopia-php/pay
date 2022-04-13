<?php

namespace Utopia\Pay\Adapter;

use Utopia\Pay\Adapter;

class Razorpay extends Adapter {

    private string $baseUrl = 'https://api.razorpay.com/v1';
    private string $keySecret;
    private string $keyId;


    public function __construct(string $keyId, string $keySecret)
    {
        $this->keySecret = $keySecret;
        $this->keyId = $keyId;
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
    public function purchase(float $amount, string $customerId, string $cardId = null, array $additonalParams = []) : array {
        $requestBody = [
            'customer' => $customerId,
            'amount' => $amount,
            'currency' => $this->currency,
        ];
        $requestBody = array_merge($requestBody, $additonalParams);
        if(!empty($cardId)) {
            $requestBody['source'] = $cardId;
        }
        $res = $this->execute('POST', '/charges', $requestBody, array('content-type: application/x-www-form-urlencoded'));
        return $res;
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
        $result = $this->execute('POST', $path, $requestBody, array('Content-Type: application/json'));
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
    public function updateCustomer(string $customerId, string $name, string $email,  array $billingDetails = [], string $paymentMethod) : bool {
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
            CURLOPT_POSTFIELDS => \json_encode($requestBody),
            CURLOPT_HEADEROPT => \CURLHEADER_UNIFIED,
            CURLOPT_USERPWD => $this->keyId . ':' . $this->keySecret,
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