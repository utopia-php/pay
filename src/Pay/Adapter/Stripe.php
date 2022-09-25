<?php

namespace Utopia\Pay\Adapter;

use Utopia\Pay\Adapter;
use Utopia\Pay\Address;

class Stripe extends Adapter
{
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
    public function getName(): string
    {
        return 'Stripe';
    }

    /**
     * Make a purchase request
     */
    public function purchase(int $amount, string $customerId, string $cardId = null, array $additonalParams = []): array
    {
        $path = '/charges';

        $requestBody = [
            'customer' => $customerId,
            'amount' => $amount,
            'currency' => $this->currency,
        ];
        $requestBody = array_merge($requestBody, $additonalParams);
        if (!empty($cardId)) {
            $requestBody['source'] = $cardId;
        }
        $res = $this->execute(self::METHOD_POST, $path, $requestBody);
        return $res;
    }

    /**
     * Refund payment
     */
    public function refund(string $paymentId, int $amount = null): array
    {
        $path = '/refunds';
        $requestBody = ['charge' => $paymentId];
        if ($amount != null) {
            $requestBody['amount'] = $amount;
        }
        return $this->execute(self::METHOD_POST, $path, $requestBody);
    }

    /**
     * Add a credit card for customer
     */
    public function createCard(string $customerId, string $cardId): array
    {
        $path = '/customers/' . $customerId . '/sources';
        return $this->execute(self::METHOD_POST, $path, ['source' => $cardId]);
    }

    /**
     * List cards
     */
    public function listCards(string $customerId): array
    {
        $path = '/customers/' . $customerId . '/sources';
        return $this->execute(self::METHOD_GET, $path);
    }

    /**
     * Update card
     */
    public function updateCard(string $customerId, string $cardId, string $name = null, int $expMonth = null, int $expYear = null, Address $billingAddress = null): array
    {
        $path = '/customers/' . $customerId . '/sources/' . $cardId;
        $requestBody = [];
        if (!empty($name)) {
            $requestBody['name'] = $name;
        }
        if (!empty($expMonth)) {
            $requestBody['exp_month'] = $expMonth;
        }
        if (!empty($expYear)) {
            $requestBody['exp_year'] = $expYear;
        }
        if (!is_null($billingAddress)) {
            $requestBody['address_city'] = $billingAddress->getCity() ?? null;
            $requestBody['address_country'] = $billingAddress->getCountry() ?? null;
            $requestBody['address_line1'] = $billingAddress->getLine1();
            $requestBody['address_line2'] = $billingAddress->getLine2();
            $requestBody['address_state'] = $billingAddress->getState();
            $requestBody['address_zip'] = $billingAddress->getPostalCode();
        }
        return $this->execute(self::METHOD_POST, $path, $requestBody);
    }

    /**
     * Get a card
     */
    public function getCard(string $customerId, string $cardId): array
    {
        $path = '/customers/' . $customerId . '/sources/' . $cardId;
        return $this->execute(self::METHOD_GET, $path);
    }

    /**
     * Delete a credit card record
     */
    public function deleteCard(string $customerId, string $cardId): bool
    {
        $path = '/customers/' . $customerId . '/sources/' . $cardId;
        $res =  $this->execute(self::METHOD_DELETE, $path);
        return $res['deleted'] ?? false;
    }

    /**
     * Add new customer in the gateway database
     * returns the newly created customer
     *
     * @throws Exception
     */
    public function createCustomer(string $name, string $email, Address $address = null, string $paymentMethod = null): array
    {
        $path = '/customers';
        $requestBody = [
            'name' => $name,
            'email' => $email,
        ];
        if (!empty($paymentMethod)) {
            $requestBody['payment_method'] = $paymentMethod;
        }
        if (!is_null($address)) {
            $requestBody['address'] = $address->asArray();
        }
        $result = $this->execute(self::METHOD_POST, $path, $requestBody);
        return $result;
    }

    /**
     * List customers
     */
    public function listCustomers(): array
    {
        return $this->execute(self::METHOD_GET, '/customers');
    }

    /**
     * Get customer details by ID
     */
    public function getCustomer(string $customerId): array
    {
        $path = '/customers/' . $customerId;
        $result = $this->execute(self::METHOD_GET, $path);
        return $result;
    }

    /**
     * Update customer details
     */
    public function updateCustomer(string $customerId, string $name, string $email, Address $address = null, string $paymentMethod = null): array
    {
        $path = '/customers/' . $customerId;
        $requestBody = [
            'name' => $name,
            'email' => $email,
        ];
        if (!empty($paymentMethod)) {
            $requestBody['payment_method'] = $paymentMethod;
        }
        if (!is_null($address)) {
            $requestBody['address'] = $address->asArray();
        }
        return $this->execute(self::METHOD_POST, $path, $requestBody);
    }

    /**
     * Delete customer by ID
     */
    public function deleteCustomer(string $customerId): bool
    {
        $path = '/customers/' . $customerId;
        $result = $this->execute(self::METHOD_DELETE, $path);
        return $result['deleted'] ?? false;
    }

    private function execute(string $method, string $path, array $requestBody = [], array $headers = []): array
    {
        $headers = array_merge(['content-type' => 'application/x-www-form-urlencoded'], $headers);
        return $this->call($method, $this->baseUrl . $path, $requestBody, $headers, [CURLOPT_USERPWD => $this->secretKey . ':']);
    }
}
