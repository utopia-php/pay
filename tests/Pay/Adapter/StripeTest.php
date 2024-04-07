<?php

namespace Utopia\Tests;

use PHPUnit\Framework\TestCase;
use Utopia\Pay\Adapter\Stripe;

class StripeTest extends TestCase
{
    private Stripe $stripe;

    protected function setUp(): void
    {
        $secretKey = getenv('STRIPE_SECRET') ? getenv('STRIPE_SECRET') : '';
        $publishableKey = getenv('STRIPE_PUBLISHABLE') ? getenv('STRIPE_PUBLISHABLE') : '';
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
        $customer = $this->stripe->createCustomer('Test customer', 'testcustomer@email.com', ['city' => 'Kathmandu', 'country' => 'NP', 'line1' => 'Gaurighat', 'line2' => 'Pambu Marga', 'postal_code' => '44600', 'state' => 'Bagmati']);
        $this->assertNotEmpty($customer['id']);
        $this->assertEquals($customer['name'], 'Test customer');
        $this->assertEquals($customer['email'], 'testcustomer@email.com');

        return ['customerId' => $customer['id']];
    }

    /** @depends testCreateCustomer */
    public function testGetCustomer(array $data): array
    {
        $customerId = $data['customerId'];
        $customer = $this->stripe->getCustomer($customerId);
        $this->assertNotEmpty($customer['id']);
        $this->assertEquals($customer['name'], 'Test customer');
        $this->assertEquals($customer['email'], 'testcustomer@email.com');

        return $data;
    }

    /** @depends testCreateCustomer */
    public function testUpdateCustomer(array $data): array
    {
        $customerId = $data['customerId'];
        $customer = $this->stripe->updateCustomer($customerId, 'Test Updated', 'testcustomerupdated@email.com');
        $this->assertNotEmpty($customer['id']);
        $this->assertEquals($customer['name'], 'Test Updated');
        $this->assertEquals($customer['email'], 'testcustomerupdated@email.com');

        return $data;
    }

    /** @depends testUpdateCustomer */
    public function testListCustomers(array $data)
    {
        $response = $this->stripe->listCustomers();
        $this->assertIsArray($response['data']);
        $this->assertNotEmpty($response['data']);
        $customers = $response['data'];
        $this->assertNotEmpty($customers[0]['id']);
        $this->assertEquals($customers[0]['name'], 'Test Updated');
        $this->assertEquals($customers[0]['email'], 'testcustomerupdated@email.com');
    }

    /** @depends testUpdateCustomer */
    public function testCreatePaymentMethod(array $data)
    {
        $customerId = $data['customerId'];
        $pm = $this->stripe->createPaymentMethod($customerId, 'card', [
            'number' => 4242424242424242,
            'exp_month' => 8,
            'exp_year' => 2030,
            'cvc' => 123,
        ]);
        $this->assertNotEmpty($pm['id']);
        $this->assertNotEmpty($pm['card']);

        $card = $pm['card'];
        $this->assertEquals('visa', $card['brand']);
        $this->assertEquals('US', $card['country']);
        $this->assertEquals(2030, $card['exp_year']);
        $this->assertEquals(8, $card['exp_month']);
        $this->assertEquals(4242, $card['last4']);

        $data['paymentMethodId'] = $pm['id'];

        return $data;
    }

    /** @depends testCreatePaymentMethod */
    public function testListPaymentMethods(array $data)
    {
        $customerId = $data['customerId'];
        $pms = $this->stripe->listPaymentMethods($customerId);
        $this->assertIsArray($pms['data']);

        $pm = $pms['data'][0];
        $this->assertNotEmpty($pm['id']);
        $this->assertNotEmpty($pm['card']);

        $card = $pm['card'];
        $this->assertEquals('visa', $card['brand']);
        $this->assertEquals('US', $card['country']);
        $this->assertEquals(2030, $card['exp_year']);
        $this->assertEquals(8, $card['exp_month']);
        $this->assertEquals(4242, $card['last4']);

        return $data;
    }

    /** @depends testCreatePaymentMethod */
    public function testGetPaymentMethod(array $data)
    {
        $customerId = $data['customerId'];
        $paymentMethodId = $data['paymentMethodId'];
        $pm = $this->stripe->getPaymentMethod($customerId, $paymentMethodId);
        $this->assertNotEmpty($pm['id']);
        $this->assertNotEmpty($pm['card']);

        $card = $pm['card'];
        $this->assertEquals('visa', $card['brand']);
        $this->assertEquals('US', $card['country']);
        $this->assertEquals(2030, $card['exp_year']);
        $this->assertEquals(8, $card['exp_month']);
        $this->assertEquals(4242, $card['last4']);

        return $data;
    }

    /** @depends testCreatePaymentMethod */
    public function testCreateFuturePayment(array $data)
    {
        $customerId = $data['customerId'];
        $setupIntent = $this->stripe->createFuturePayment($customerId, paymentMethodOptions: [
            'card' => [
                'mandate_options' => [
                    'reference' => \uniqid(),
                    'description' => 'Utopia pay test',
                    'amount' => 15000,
                    'currency' => 'USD',
                    'start_date' => time(),
                    'amount_type' => 'maximum',
                    'interval' => 'day',
                    'interval_count' => 30,
                    'supported_types' => ['india'],
                ],
            ],
        ]);
        $this->assertNotEmpty($setupIntent);
        $this->assertNotEmpty($setupIntent['client_secret']);
        $data['setupIntentId'] = $setupIntent['id'];

        return $data;
    }

    /** @depends testCreateFuturePayment */
    public function testUpdateFuturePayment(array $data)
    {
        $customerId = $data['customerId'];
        $setupIntentId = $data['setupIntentId'];

        $reference = uniqid();
        $setupIntent = $this->stripe->updateFuturePayment($setupIntentId, $customerId, paymentMethodOptions: [
            'card' => [
                'mandate_options' => [
                    'reference' => $reference,
                    'description' => 'Utopia monthly subscription',
                    'amount' => 1500,
                    'currency' => 'USD',
                    'start_date' => time(),
                    'amount_type' => 'maximum',
                    'interval' => 'day',
                    'interval_count' => 5,
                    'supported_types' => ['india'],
                ],
            ],
        ]);

        $this->assertNotEmpty($setupIntent);
        $this->assertEquals($setupIntentId, $setupIntent['id']);
        $this->assertIsArray($setupIntent['payment_method_options']);
        $this->assertArrayHasKey('card', $setupIntent['payment_method_options']);
        $this->assertArrayHasKey('mandate_options', $setupIntent['payment_method_options']['card']);
        $this->assertEquals($reference, $setupIntent['payment_method_options']['card']['mandate_options']['reference']);
    }

    /** @depends testCreateFuturePayment */
    public function testListFuturePayment(array $data)
    {
        $customerId = $data['customerId'];
        $setupIntentId = $data['setupIntentId'];

        $setupIntents = $this->stripe->listFuturePayments($customerId);
        $this->assertNotEmpty($setupIntents);
        $this->assertCount(1, $setupIntents);
        $this->assertEquals($setupIntentId, $setupIntents[0]['id']);
    }

    /** @depends testCreatePaymentMethod */
    public function testUpdatePaymentMethod(array $data)
    {
        $paymentMethodId = $data['paymentMethodId'];
        $pm = $this->stripe->updatePaymentMethod($paymentMethodId, 'card', [
            'exp_month' => 6,
            'exp_year' => 2031,
        ]);
        $this->assertNotEmpty($pm['id']);
        $this->assertNotEmpty($pm['card']);

        $card = $pm['card'];
        $this->assertEquals(2031, $card['exp_year']);
        $this->assertEquals(6, $card['exp_month']);

        return $data;
    }

    /** @depends testCreatePaymentMethod */
    public function testPurchase(array $data)
    {
        $customerId = $data['customerId'];
        $paymentMethodId = $data['paymentMethodId'];
        $purchase = $this->stripe->purchase(5000, $customerId, $paymentMethodId);

        $this->assertNotEmpty($purchase['id']);
        $this->assertEquals(5000, $purchase['amount_received']);
        $this->assertEquals('payment_intent', $purchase['object']);
        $this->assertEquals('succeeded', $purchase['status']);

        $data['paymentId'] = $purchase['id'];

        return $data;
    }

    /** @depends testPurchase */
    public function testRefund(array $data)
    {
        $purchase = $this->stripe->refund($data['paymentId'], 3000);
        $this->assertNotEmpty($purchase['id']);
        $this->assertEquals('refund', $purchase['object']);
        $this->assertEquals('succeeded', $purchase['status']);
        $this->assertEquals(3000, $purchase['amount']);
    }

    /** @depends testCreatePaymentMethod */
    public function testDeletePaymentMethod(array $data)
    {
        $customerId = $data['customerId'];
        $deleted = $this->stripe->deletePaymentMethod($data['paymentMethodId']);
        $this->assertTrue($deleted);

        $this->expectException('Exception');
        $this->expectExceptionCode(404);
        $this->stripe->getPaymentMethod($customerId, $data['paymentMethodId']);
    }

    /** @depends testUpdateCustomer */
    public function testDeleteCustomer(array $data)
    {
        $customerId = $data['customerId'];
        $deleted = $this->stripe->deleteCustomer($customerId);
        $this->assertTrue($deleted);
        $res = $this->stripe->getCustomer($customerId);
        $this->assertTrue($res['deleted']);
    }
}
