<?php

namespace Utopia\Tests;

use Utopia\Pay\Adapter\Stripe;
use PHPUnit\Framework\TestCase;


class StripeTest extends TestCase {

    private Stripe $stripe;

    protected function setUp(): void
    {
        $secretKey = $_SERVER['STRIPE_SECRET'] ?? 'sk_test_4eC39HqLyjWDarjtT1zdp7dc';
        $publishableKey = $_SERVER['STRIPE_PUBLISHABLE'] ?? '';
        $this->stripe = new Stripe(
            $secretKey,
            $publishableKey
        );
    }

    public function testName()
    {
        $this->assertEquals($this->stripe->getName(), 'Stripe');
    }

    public function testCreateCustomer(): array
    {
        $customer = $this->stripe->createCustomer('Test customer', 'testcustomer@email.com');
        $this->assertNotEmpty($customer['id']);
        $this->assertEquals($customer['name'], 'Test customer');
        $this->assertEquals($customer['email'], 'testcustomer@email.com');
        return ['customerId' => $customer['id']];
    }

    /** @depends testCreateCustomer */
    public function testGetCustomer(array $data): array {
        $customerId = $data['customerId'];
        $customer = $this->stripe->getCustomer($customerId);
        $this->assertNotEmpty($customer['id']);
        $this->assertEquals($customer['name'], 'Test customer');
        $this->assertEquals($customer['email'], 'testcustomer@email.com');
        return $data;
    }

    public function testListCustomers() {
        $response = $this->stripe->listCustomers();
        $this->assertIsArray($response['data']);
        $this->assertNotEmpty($response['data']);
        $customers = $response['data'];
        $this->assertNotEmpty($customers[0]['id']);
        $this->assertEquals($customers[0]['name'], 'Test customer');
        $this->assertEquals($customers[0]['email'], 'testcustomer@email.com');
    }
    


}