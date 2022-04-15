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
            $publishableKey,
            $secretKey
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

    /** @depends testCreateCustomer */
    public function testUpdateCustomer(array $data): array {
        $customerId = $data['customerId'];
        $customer = $this->stripe->updateCustomer($customerId, 'Test Updated', 'testcustomerupdated@email.com');
        $this->assertNotEmpty($customer['id']);
        $this->assertEquals($customer['name'], 'Test Updated');
        $this->assertEquals($customer['email'], 'testcustomerupdated@email.com');
        return $data;
    }

    public function testListCustomers() {
        $response = $this->stripe->listCustomers();
        $this->assertIsArray($response['data']);
        $this->assertNotEmpty($response['data']);
        $customers = $response['data'];
        $this->assertNotEmpty($customers[0]['id']);
        $this->assertEquals($customers[0]['name'], 'Test Updated');
        $this->assertEquals($customers[0]['email'], 'testcustomerupdated@email.com');
    }

    /** @depends testUpdateCustomer */
    public function testCreateCard(array $data) {
        $customerId = $data['customerId'];
        $card = $this->stripe->createCard($customerId, 'tok_visa');
        $this->assertNotEmpty($card['id']);
        $this->assertEquals('Visa', $card['brand']);
        $this->assertEquals('US', $card['country']);
        $this->assertEquals(2023, $card['exp_year']);
        $this->assertEquals(4, $card['exp_month']);
        $data['cardId'] = $card['id'];
        return $data;
    }
    
    /** @depends testCreateCard */
    public function testListCards(array $data) {
        $customerId = $data['customerId'];
        $cards = $this->stripe->listCards($customerId);
        $card = $cards['data'][0];
        $this->assertNotEmpty($card['id']);
        $this->assertEquals('Visa', $card['brand']);
        $this->assertEquals('US', $card['country']);
        $this->assertEquals(2023, $card['exp_year']);
        $this->assertEquals(4, $card['exp_month']);
        return $data;
    }

    /** @depends testCreateCard */
    public function testGetCard(array $data) {
        $customerId = $data['customerId'];
        $card = $this->stripe->getCard($customerId, $data['cardId']);
        $this->assertNotEmpty($card['id']);
        $this->assertEquals('Visa', $card['brand']);
        $this->assertEquals('US', $card['country']);
        $this->assertEquals(2023, $card['exp_year']);
        $this->assertEquals(4, $card['exp_month']);
        return $data;
    }

    /** @depends testCreateCard */
    public function testUpdateCard(array $data) {
        $customerId = $data['customerId'];
        $card = $this->stripe->updateCard($customerId, $data['cardId'], 'Test Customer', 5, 2024);
        $this->assertNotEmpty($card['id']);
        $this->assertEquals('Visa', $card['brand']);
        $this->assertEquals('US', $card['country']);
        $this->assertEquals(2024, $card['exp_year']);
        $this->assertEquals(5, $card['exp_month']);
        $this->assertEquals('Test Customer', $card['name']);
        return $data;
    }

    /** @depends testCreateCard */
    public function testDeleteCard(array $data) {
        $customerId = $data['customerId'];
        $deleted = $this->stripe->deleteCard($customerId, $data['cardId']);
        $this->assertTrue($deleted);
        $res = $this->stripe->getCard($customerId, $data['cardId']);
        $this->assertEquals('resource_missing', $res['error']['code']);
        return $data;
    }

    /** @depends testUpdateCustomer */
    public function testDeleteCustomer(array $data) {
        $customerId = $data['customerId'];
        $deleted = $this->stripe->deleteCustomer($customerId);
        $this->assertTrue($deleted);
        $res = $this->stripe->getCustomer($customerId);
        $this->assertTrue($res['deleted']);
    }
}