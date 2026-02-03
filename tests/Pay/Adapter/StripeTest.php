<?php

namespace Utopia\Tests;

use PHPUnit\Framework\TestCase;
use Utopia\Pay\Adapter\Stripe;
use Utopia\Pay\Address;
use Utopia\Pay\Exception;

class StripeTest extends TestCase
{
    private Stripe $stripe;

    protected function setUp(): void
    {
        $secretKey = getenv('STRIPE_SECRET') ? getenv('STRIPE_SECRET') : '';
        $this->stripe = new Stripe(
            $secretKey
        );
    }

    public function testName(): void
    {
        $this->assertEquals($this->stripe->getName(), 'Stripe');
    }

    /**
     * Test create customer
     *
     * @return array<mixed>
     */
    public function testCreateCustomer(): array
    {
        $address = new Address('Kathmandu', 'NP', 'Gaurighat', 'Pambu Marga', '44600', 'Bagmati');
        $customer = $this->stripe->createCustomer('Test customer', 'testcustomer@email.com', $address);
        $this->assertNotEmpty($customer->getId());
        $this->assertEquals('Test customer', $customer->getName());
        $this->assertEquals('testcustomer@email.com', $customer->getEmail());

        return ['customerId' => $customer->getId()];
    }

    /**
     * @depends testCreateCustomer
     *
     * @param  array<mixed>  $data
     * @return array<mixed>
     */
    public function testGetCustomer(array $data): array
    {
        $customerId = $data['customerId'];
        $customer = $this->stripe->getCustomer($customerId);
        $this->assertNotEmpty($customer->getId());
        $this->assertEquals('Test customer', $customer->getName());
        $this->assertEquals('testcustomer@email.com', $customer->getEmail());

        return $data;
    }

    /**
     * @depends testCreateCustomer
     *
     * @param  array<mixed>  $data
     * @return array<mixed>
     */
    public function testUpdateCustomer(array $data): array
    {
        $customerId = $data['customerId'];
        $customer = $this->stripe->updateCustomer($customerId, 'Test Updated', 'testcustomerupdated@email.com');
        $this->assertNotEmpty($customer->getId());
        $this->assertEquals('Test Updated', $customer->getName());
        $this->assertEquals('testcustomerupdated@email.com', $customer->getEmail());

        return $data;
    }

    /**
     * @depends testUpdateCustomer
     *
     * @param  array<mixed>  $data
     */
    public function testListCustomers(array $data): void
    {
        $customers = $this->stripe->listCustomers();
        $this->assertIsArray($customers);
        $this->assertNotEmpty($customers);
        $this->assertNotEmpty($customers[0]->getId());
        $this->assertNotEmpty($customers[0]->getName());
        $this->assertNotEmpty($customers[0]->getEmail());
    }

    /**
     * @depends testUpdateCustomer
     *
     * @param  array<mixed>  $data
     * @return array<mixed>
     */
    public function testCreatePaymentMethod(array $data): array
    {
        $customerId = $data['customerId'];
        $pm = $this->stripe->createPaymentMethod($customerId, 'card', [
            'number' => 4242424242424242,
            'exp_month' => 8,
            'exp_year' => 2030,
            'cvc' => 123,
        ]);
        $this->assertNotEmpty($pm->getId());
        $this->assertTrue($pm->isCard());

        $this->assertEquals('visa', $pm->getBrand());
        $this->assertEquals('US', $pm->getCountry());
        $this->assertEquals(2030, $pm->getExpiryYear());
        $this->assertEquals(8, $pm->getExpiryMonth());
        $this->assertEquals('4242', $pm->getLast4());

        $data['paymentMethodId'] = $pm->getId();

        return $data;
    }

    /**
     * @depends testCreatePaymentMethod
     *
     * @param  array<mixed>  $data
     * @return array<mixed>
     */
    public function testListPaymentMethods(array $data): array
    {
        $customerId = $data['customerId'];
        $pms = $this->stripe->listPaymentMethods($customerId);
        $this->assertIsArray($pms);
        $this->assertNotEmpty($pms);

        $pm = $pms[0];
        $this->assertNotEmpty($pm->getId());
        $this->assertTrue($pm->isCard());

        $this->assertEquals('visa', $pm->getBrand());
        $this->assertEquals('US', $pm->getCountry());
        $this->assertEquals(2030, $pm->getExpiryYear());
        $this->assertEquals(8, $pm->getExpiryMonth());
        $this->assertEquals('4242', $pm->getLast4());

        return $data;
    }

    /** @depends testCreatePaymentMethod
     * @param  array<mixed>  $data
     * @return array<mixed>
     */
    public function testGetPaymentMethod(array $data): array
    {
        $customerId = $data['customerId'];
        $paymentMethodId = $data['paymentMethodId'];
        $pm = $this->stripe->getPaymentMethod($customerId, $paymentMethodId);
        $this->assertNotEmpty($pm->getId());
        $this->assertTrue($pm->isCard());

        $this->assertEquals('visa', $pm->getBrand());
        $this->assertEquals('US', $pm->getCountry());
        $this->assertEquals(2030, $pm->getExpiryYear());
        $this->assertEquals(8, $pm->getExpiryMonth());
        $this->assertEquals('4242', $pm->getLast4());

        return $data;
    }

    /**
     * @depends testCreatePaymentMethod
     *
     * @param  array<mixed>  $data
     * @return array<mixed>
     */
    public function testCreateFuturePayment(array $data): array
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

    /**
     * @depends testCreateFuturePayment
     *
     * @param  array<mixed>  $data
     * */
    public function testUpdateFuturePayment(array $data): void
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

    /**
     * @depends testCreateFuturePayment
     *
     * @param  array<mixed>  $data
     * */
    public function testListFuturePayment(array $data): void
    {
        $customerId = $data['customerId'];
        $setupIntentId = $data['setupIntentId'];

        $setupIntents = $this->stripe->listFuturePayments($customerId);
        $this->assertNotEmpty($setupIntents);
        $this->assertNotEmpty($setupIntents[0]['id']);
    }

    /**
     * @depends testCreatePaymentMethod
     *
     * @param  array<mixed>  $data
     * @return array<mixed>
     * */
    public function testUpdatePaymentMethod(array $data): array
    {
        $paymentMethodId = $data['paymentMethodId'];
        $pm = $this->stripe->updatePaymentMethod($paymentMethodId, 'card', [
            'exp_month' => 6,
            'exp_year' => 2031,
        ]);
        $this->assertNotEmpty($pm->getId());
        $this->assertTrue($pm->isCard());

        $this->assertEquals(2031, $pm->getExpiryYear());
        $this->assertEquals(6, $pm->getExpiryMonth());

        return $data;
    }

    /**
     * @depends testCreatePaymentMethod
     *
     * @param  array<mixed>  $data
     * @return array<mixed>
     * */
    public function testPurchase(array $data): array
    {
        $customerId = $data['customerId'];
        $paymentMethodId = $data['paymentMethodId'];
        $purchase = $this->stripe->purchase(5000, $customerId, $paymentMethodId);

        $this->assertNotEmpty($purchase->getId());
        $this->assertEquals(5000, $purchase->getAmountReceived());
        $this->assertTrue($purchase->isSucceeded());

        $data['paymentId'] = $purchase->getId();

        return $data;
    }

    /**
     * Test retryPurchase: create a payment with a failing payment method, then retry with a succeeding one.
     *
     * @depends testCreateCustomer
     *
     * @param  array<mixed>  $data
     * @return array<mixed>
     */
    public function testRetryPurchase(array $data): array
    {
        $customerId = $data['customerId'];
        // Create a payment method that will fail (card_declined)
        $failingPm = $this->stripe->createPaymentMethod($customerId, 'card', [
            'number' => '4000000000000341',
            'exp_month' => 8,
            'exp_year' => 2030,
            'cvc' => 123,
        ]);
        $this->assertNotEmpty($failingPm->getId());
        $failingPmId = $failingPm->getId();

        // Create a payment intent with the failing payment method
        $paymentIntentId = null;
        try {
            $this->stripe->purchase(5000, $customerId, $failingPmId);
            $this->fail('Expected payment to fail');
        } catch (Exception $e) {
            $this->assertEquals(Exception::GENERIC_DECLINE, $e->getType());
            $this->assertEquals(402, $e->getCode());
            $paymentIntentMeta = $e->getMetadata()['payment_intent'] ?? null;
            $paymentIntentId = is_array($paymentIntentMeta) && isset($paymentIntentMeta['id']) ? $paymentIntentMeta['id'] : $paymentIntentMeta;
            $this->assertNotEmpty($paymentIntentId);
        }

        // Create a succeeding payment method
        $succeedingPm = $this->stripe->createPaymentMethod($customerId, 'card', [
            'number' => '4242424242424242', // Stripe test card: always succeeds
            'exp_month' => 8,
            'exp_year' => 2030,
            'cvc' => 123,
        ]);
        $this->assertNotEmpty($succeedingPm->getId());
        $succeedingPmId = $succeedingPm->getId();

        // Retry the payment intent with the succeeding payment method
        $result = $this->stripe->retryPurchase((string) $paymentIntentId, $succeedingPmId);
        $this->assertNotEmpty($result->getId());
        $this->assertEquals($paymentIntentId, $result->getId());
        $this->assertTrue($result->isSucceeded());

        // Save for further tests if needed
        $data['paymentId'] = $paymentIntentId;
        $data['paymentMethodId'] = $succeedingPmId;

        return $data;
    }

    /**
     * @depends testPurchase
     */
    public function testGetPayment(array $data): array
    {
        $paymentId = $data['paymentId'];
        $payment = $this->stripe->getPayment($paymentId);
        $this->assertNotEmpty($payment->getId());
        $this->assertEquals(5000, $payment->getAmountReceived());
        $this->assertTrue($payment->isSucceeded());

        return $data;
    }

    /**
     * Test updatePayment: create a payment intent in a non-succeeded state, update its payment method and amount, and assert the update.
     *
     * @depends testCreateCustomer
     *
     * @param  array<mixed>  $data
     * @return void
     */
    public function testUpdatePayment(array $data): void
    {
        $customerId = $data['customerId'];
        // Create a payment method that will fail (card_declined)
        $failingPm = $this->stripe->createPaymentMethod($customerId, 'card', [
            'number' => '4000000000000341',
            'exp_month' => 8,
            'exp_year' => 2030,
            'cvc' => 123,
        ]);
        $this->assertNotEmpty($failingPm->getId());
        $failingPmId = $failingPm->getId();

        // Create a payment intent with the failing payment method
        $paymentIntentId = null;
        try {
            $this->stripe->purchase(5000, $customerId, $failingPmId);
            $this->fail('Expected payment to fail');
        } catch (Exception $e) {
            $this->assertEquals(Exception::GENERIC_DECLINE, $e->getType());
            $this->assertEquals(402, $e->getCode());
            $paymentIntentMeta = $e->getMetadata()['payment_intent'] ?? null;
            $paymentIntentId = is_array($paymentIntentMeta) && isset($paymentIntentMeta['id']) ? $paymentIntentMeta['id'] : $paymentIntentMeta;
            $this->assertNotEmpty($paymentIntentId);
        }

        // Create a succeeding payment method
        $succeedingPm = $this->stripe->createPaymentMethod($customerId, 'card', [
            'number' => '4242424242424242',
            'exp_month' => 8,
            'exp_year' => 2030,
            'cvc' => 123,
        ]);
        $this->assertNotEmpty($succeedingPm->getId());
        $succeedingPmId = $succeedingPm->getId();

        // Update the payment intent with the new payment method and amount
        $newAmount = 6000;
        $updated = $this->stripe->updatePayment((string) $paymentIntentId, $succeedingPmId, $newAmount);
        $this->assertNotEmpty($updated->getId());
        $this->assertEquals($paymentIntentId, $updated->getId());
        $this->assertEquals($newAmount, $updated->getAmount());
        $this->assertEquals($succeedingPmId, $updated->getPaymentMethodId());
    }

    /**
     * @depends testPurchase
     *
     * @param  array<mixed>  $data
     */
    public function testRefund(array $data): void
    {
        $refund = $this->stripe->refund($data['paymentId'], 3000);
        $this->assertNotEmpty($refund->getId());
        $this->assertTrue($refund->isSucceeded());
        $this->assertEquals(3000, $refund->getAmount());
    }

    /**
     * @depends testCreatePaymentMethod
     *
     * @param  array<mixed>  $data
     */
    public function testDeletePaymentMethod(array $data): void
    {
        $customerId = $data['customerId'];
        $deleted = $this->stripe->deletePaymentMethod($data['paymentMethodId']);
        $this->assertTrue($deleted);

        try {
            $this->stripe->getPaymentMethod($customerId, $data['paymentMethodId']);
            $this->fail('Expected exception was not thrown');
        } catch (\Exception $e) {
            $this->assertInstanceOf(\Exception::class, $e);
            $this->assertEquals(404, $e->getCode());
        }
    }

    /**
     * @depends testUpdateCustomer
     *
     * @param  array<mixed>  $data
     */
    public function testDeleteCustomer(array $data): void
    {
        $customerId = $data['customerId'];
        $deleted = $this->stripe->deleteCustomer($customerId);
        $this->assertTrue($deleted);
        $customer = $this->stripe->getCustomer($customerId);
        $this->assertTrue($customer->isDeleted());
    }

    /**
     * Test list disputes
     *
     * @return void
     */
    public function testListDisputes(): void
    {
        $address = new Address('Kathmandu', 'NP', 'Gaurighat', 'Pambu Marga', '44600', 'Bagmati');
        $customer = $this->stripe->createCustomer('Test customer', 'testcustomer@email.com', $address);
        $this->assertNotEmpty($customer->getId());
        $customerId = $customer->getId();

        $pm = $this->stripe->createPaymentMethod($customerId, 'card', [
            'number' => 4000000000000259,
            'exp_month' => 8,
            'exp_year' => 2030,
            'cvc' => 123,
        ]);
        $this->assertNotEmpty($pm->getId());
        $this->assertTrue($pm->isCard());

        $this->assertEquals('visa', $pm->getBrand());
        $this->assertEquals('US', $pm->getCountry());
        $this->assertEquals(2030, $pm->getExpiryYear());
        $this->assertEquals(8, $pm->getExpiryMonth());
        $this->assertEquals('0259', $pm->getLast4());

        $paymentMethodId = $pm->getId();

        $purchase = $this->stripe->purchase(5000, $customerId, $paymentMethodId);

        $this->assertNotEmpty($purchase->getId());
        $this->assertEquals(5000, $purchase->getAmountReceived());
        $this->assertTrue($purchase->isSucceeded());

        // list disputes
        $paymentIntentId = $purchase->getId();

        $disputes = $this->stripe->listDisputes(1);
        $this->assertIsArray($disputes);
        $this->assertEquals(1, count($disputes));

        $disputes = $this->stripe->listDisputes(paymentIntentId: $paymentIntentId);
        $this->assertEquals(1, count($disputes));
        $this->assertEquals($paymentIntentId, $disputes[0]['payment_intent']);
    }

    public function testErrorHandling(): void
    {
        try {
            $this->stripe->deleteCustomer('dedefe');
        } catch (\Throwable $e) {
            $this->assertEquals(404, $e->getCode());
            $this->assertInstanceOf(Exception::class, $e);
        }

        $address = new Address('Kathmandu', 'NP', 'Gaurighat', 'Pambu Marga', '44600', 'Bagmati');
        $customer = $this->stripe->createCustomer('Test customer', 'testcustomer@email.com', $address);
        $this->assertNotEmpty($customer->getId());

        $customerId = $customer->getId();

        // incorrect card number
        try {
            $pm = $this->stripe->createPaymentMethod($customerId, 'card', [
                'number' => 4242424242424241,
                'exp_month' => 8,
                'exp_year' => 2030,
                'cvc' => 123,
            ]);
        } catch (Exception $e) {
            $this->assertEquals(402, $e->getCode());
            $this->assertEquals(Exception::INCORRECT_NUMBER, $e->getType());
            $this->assertInstanceOf(Exception::class, $e);
        }

        // insufficient fund
        try {
            $pm = $this->stripe->createPaymentMethod($customerId, 'card', [
                'number' => 4000000000009995,
                'exp_month' => 8,
                'exp_year' => 2030,
                'cvc' => 123,
            ]);
        } catch (Exception $e) {
            $this->assertEquals(402, $e->getCode());
            $this->assertEquals(Exception::INSUFFICIENT_FUNDS, $e->getType());
            $this->assertInstanceOf(Exception::class, $e);
        }

        // authentication required
        try {
            $pm = $this->stripe->createPaymentMethod($customerId, 'card', [
                'number' => 4000002760003184,
                'exp_month' => 8,
                'exp_year' => 2030,
                'cvc' => 123,
            ]);
        } catch (Exception $e) {
            $this->assertEquals(402, $e->getCode());
            $this->assertEquals(Exception::AUTHENTICATION_REQUIRED, $e->getType());
            $this->assertNotEmpty($e->getMetadata());
            $this->assertEquals(Exception::AUTHENTICATION_REQUIRED, $e->getMetadata()['decline_code']);
            $this->assertInstanceOf(Exception::class, $e);
        }

        // generic decline
        try {
            $pm = $this->stripe->createPaymentMethod($customerId, 'card', [
                'number' => 4000000000000002,
                'exp_month' => 8,
                'exp_year' => 2030,
                'cvc' => 123,
            ]);
        } catch (Exception $e) {
            $this->assertEquals(402, $e->getCode());
            $this->assertEquals(Exception::GENERIC_DECLINE, $e->getType());
            $this->assertInstanceOf(Exception::class, $e);
        }
    }
}
