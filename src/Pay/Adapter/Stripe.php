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
    public function purchase(int $amount, string $customerId, string $paymentMethodId = null, array $additionalParams = []): array
    {
        $path = '/payment_intents';
        $requestBody = [
            'amount' => $amount,
            'currency' => $this->currency,
            'customer' => $customerId,
            'payment_method' => $paymentMethodId,
            'off_session' => 'true',
            'confirm' => 'true',
        ];

        $requestBody = array_merge($requestBody, $additionalParams);
        $result = $this->execute(self::METHOD_POST, $path, $requestBody);

        return $result;
    }

    /**
     * Refund payment
     */
    public function refund(string $paymentId, int $amount = null, string $reason = null): array
    {
        $path = '/refunds';
        $requestBody = ['payment_intent' => $paymentId];
        if ($amount != null) {
            $requestBody['amount'] = $amount;
        }

        if ($reason != null) {
            $requestBody['reason'] = $reason;
        }

        return $this->execute(self::METHOD_POST, $path, $requestBody);
    }

    /**
     * Add a credit card for customer
     */
    public function createPaymentMethod(string $customerId, string $type, array $paymentMethodDetails): array
    {
        $path = '/payment_methods';

        $requestBody = [
            'type' => $type,
            $type => $paymentMethodDetails,
        ];

        // Create payment method
        $paymentMethod = $this->execute(self::METHOD_POST, $path, $requestBody);
        $paymentMethodId = $paymentMethod['id'];

        // attach payment method to the customer
        $path .= '/'.$paymentMethodId.'/attach';

        return $this->execute(self::METHOD_POST, $path, ['customer' => $customerId]);
    }

    /**
     * List cards
     */
    public function listPaymentMethods(string $customerId): array
    {
        $path = '/customers/'.$customerId.'/payment_methods';

        return $this->execute(self::METHOD_GET, $path);
    }

    /**
     * List Customer Payment Methods
     */
    public function getPaymentMethod(string $customerId, string $paymentMethodId): array
    {
        $path = '/customers/'.$customerId.'/payment_methods/'.$paymentMethodId;

        return $this->execute(self::METHOD_GET, $path);
    }

    /**
     * Update card
     */
    public function updatePaymentMethodBillingDetails(string $paymentMethodId, string $name = null, string $email = null, string $phone = null, array $address = null): array
    {
        $path = '/payment_methods/'.$paymentMethodId;
        $requestBody = [];
        $requestBody['billing_details'] = [];
        if (! empty($name)) {
            $requestBody['billing_details']['name'] = $name;
        }
        if (! empty($email)) {
            $requestBody['billing_details']['email'] = $email;
        }
        if (! empty($phone)) {
            $requestBody['billing_details']['phone'] = $phone;
        }
        if (! is_null($address)) {
            $requestBody['billing_details']['address'] = $address;
        }

        return $this->execute(self::METHOD_POST, $path, $requestBody);
    }

    public function updatePaymentMethod(string $paymentMethodId, string $type, array $details): array
    {
        $path = '/payment_methods/'.$paymentMethodId;

        $requestBody = [
            $type => $details,
        ];

        return $this->execute(self::METHOD_POST, $path, $requestBody);
    }

    /**
     * Delete a credit card record
     */
    public function deletePaymentMethod(string $paymentMethodId): bool
    {
        $path = '/payment_methods/'.$paymentMethodId.'/detach';
        $this->execute(self::METHOD_POST, $path);

        return true;
    }

    /**
     * Add new customer in the gateway database
     * returns the newly created customer
     *
     * @throws Exception
     */
    public function createCustomer(string $name, string $email, array $address = [], string $paymentMethod = null): array
    {
        $path = '/customers';
        $requestBody = [
            'name' => $name,
            'email' => $email,
        ];
        if (! empty($paymentMethod)) {
            $requestBody['payment_method'] = $paymentMethod;
        }
        if (! is_null($address)) {
            $requestBody['address'] = $address;
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
        $path = '/customers/'.$customerId;
        $result = $this->execute(self::METHOD_GET, $path);

        return $result;
    }

    /**
     * Update customer details
     */
    public function updateCustomer(string $customerId, string $name, string $email, Address $address = null, string $paymentMethod = null): array
    {
        $path = '/customers/'.$customerId;
        $requestBody = [
            'name' => $name,
            'email' => $email,
        ];
        if (! empty($paymentMethod)) {
            $requestBody['payment_method'] = $paymentMethod;
        }
        if (! is_null($address)) {
            $requestBody['address'] = $address->asArray();
        }

        return $this->execute(self::METHOD_POST, $path, $requestBody);
    }

    /**
     * Delete customer by ID
     */
    public function deleteCustomer(string $customerId): bool
    {
        $path = '/customers/'.$customerId;
        $result = $this->execute(self::METHOD_DELETE, $path);

        return $result['deleted'] ?? false;
    }

    public function createFuturePayment(string $customerId, array $paymentMethodTypes = ['card']): array
    {
        $path = '/setup_intents';
        $requestBody = [
            'customer' => $customerId,
            'payment_method_types' => $paymentMethodTypes,
        ];

        $result = $this->execute(self::METHOD_POST, $path, $requestBody);

        return $result;
    }

    public function taxCalculations(string $invoiceId, float $amount, string $currency, array $address): array
    {
        $path = '/tax/calculations';

        $lineItems = [
            0 => [
                'amount' => $amount,
                'reference' => $invoiceId,
            ]
        ];
        $customerDetails = [
            'address' => $address,
            'address_source' => 'billing',
        ];
        $requestBody = [
            'currency' => $currency,
            'customer_details' => $customerDetails,
            'line_items' => $lineItems
        ];

        $result = $this->execute(self::METHOD_POST, $path, $requestBody);

        return $result;
    }

    private function execute(string $method, string $path, array $requestBody = [], array $headers = []): array
    {
        $headers = array_merge(['content-type' => 'application/x-www-form-urlencoded', 'Authorization' => 'Bearer '.$this->secretKey], $headers);

        return $this->call($method, $this->baseUrl.$path, $requestBody, $headers);
    }
}
