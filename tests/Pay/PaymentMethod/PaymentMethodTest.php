<?php

namespace Utopia\Tests;

use PHPUnit\Framework\TestCase;
use Utopia\Pay\Address;
use Utopia\Pay\PaymentMethod\PaymentMethod;

class PaymentMethodTest extends TestCase
{
    private PaymentMethod $paymentMethod;

    protected function setUp(): void
    {
        $this->paymentMethod = new PaymentMethod(
            'pm_123',
            PaymentMethod::TYPE_CARD,
            'cus_123',
            'visa',
            '4242'
        );
    }

    public function testConstructor(): void
    {
        $this->assertEquals('pm_123', $this->paymentMethod->getId());
        $this->assertEquals(PaymentMethod::TYPE_CARD, $this->paymentMethod->getType());
        $this->assertEquals('cus_123', $this->paymentMethod->getCustomerId());
        $this->assertEquals('visa', $this->paymentMethod->getBrand());
        $this->assertEquals('4242', $this->paymentMethod->getLast4());
    }

    public function testConstructorWithAllParameters(): void
    {
        $address = new Address('New York', 'US');
        $pm = new PaymentMethod(
            'pm_full',
            PaymentMethod::TYPE_CARD,
            'cus_456',
            'mastercard',
            '5555',
            12,
            2025,
            'credit',
            'US',
            $address,
            'John Doe',
            'john@example.com',
            '+1234567890',
            ['key' => 'value'],
            1234567890
        );

        $this->assertEquals('pm_full', $pm->getId());
        $this->assertEquals('mastercard', $pm->getBrand());
        $this->assertEquals('5555', $pm->getLast4());
        $this->assertEquals(12, $pm->getExpMonth());
        $this->assertEquals(2025, $pm->getExpYear());
        $this->assertEquals('credit', $pm->getFunding());
        $this->assertEquals('US', $pm->getCountry());
        $this->assertSame($address, $pm->getBillingAddress());
        $this->assertEquals('John Doe', $pm->getName());
        $this->assertEquals('john@example.com', $pm->getEmail());
        $this->assertEquals('+1234567890', $pm->getPhone());
        $this->assertEquals(['key' => 'value'], $pm->getMetadata());
        $this->assertEquals(1234567890, $pm->getCreatedAt());
    }

    public function testGettersAndSetters(): void
    {
        $address = new Address('Los Angeles', 'US');

        $this->paymentMethod->setId('pm_new');
        $this->paymentMethod->setType(PaymentMethod::TYPE_SEPA_DEBIT);
        $this->paymentMethod->setCustomerId('cus_new');
        $this->paymentMethod->setBrand('amex');
        $this->paymentMethod->setLast4('1234');
        $this->paymentMethod->setExpMonth(6);
        $this->paymentMethod->setExpYear(2030);
        $this->paymentMethod->setFunding('debit');
        $this->paymentMethod->setCountry('CA');
        $this->paymentMethod->setBillingAddress($address);
        $this->paymentMethod->setName('Jane Doe');
        $this->paymentMethod->setEmail('jane@example.com');
        $this->paymentMethod->setPhone('+9876543210');
        $this->paymentMethod->setMetadata(['foo' => 'bar']);
        $this->paymentMethod->setCreatedAt(9876543210);

        $this->assertEquals('pm_new', $this->paymentMethod->getId());
        $this->assertEquals(PaymentMethod::TYPE_SEPA_DEBIT, $this->paymentMethod->getType());
        $this->assertEquals('cus_new', $this->paymentMethod->getCustomerId());
        $this->assertEquals('amex', $this->paymentMethod->getBrand());
        $this->assertEquals('1234', $this->paymentMethod->getLast4());
        $this->assertEquals(6, $this->paymentMethod->getExpMonth());
        $this->assertEquals(2030, $this->paymentMethod->getExpYear());
        $this->assertEquals('debit', $this->paymentMethod->getFunding());
        $this->assertEquals('CA', $this->paymentMethod->getCountry());
        $this->assertSame($address, $this->paymentMethod->getBillingAddress());
        $this->assertEquals('Jane Doe', $this->paymentMethod->getName());
        $this->assertEquals('jane@example.com', $this->paymentMethod->getEmail());
        $this->assertEquals('+9876543210', $this->paymentMethod->getPhone());
        $this->assertEquals(['foo' => 'bar'], $this->paymentMethod->getMetadata());
        $this->assertEquals(9876543210, $this->paymentMethod->getCreatedAt());
    }

    public function testIsCard(): void
    {
        $this->assertTrue($this->paymentMethod->isCard());

        $this->paymentMethod->setType(PaymentMethod::TYPE_SEPA_DEBIT);
        $this->assertFalse($this->paymentMethod->isCard());

        $this->paymentMethod->setType(PaymentMethod::TYPE_CARD);
        $this->assertTrue($this->paymentMethod->isCard());
    }

    public function testIsExpired(): void
    {
        // Card without expiration date
        $this->assertFalse($this->paymentMethod->isExpired());

        // Card with future expiration
        $this->paymentMethod->setExpMonth(12);
        $this->paymentMethod->setExpYear(2099);
        $this->assertFalse($this->paymentMethod->isExpired());

        // Card with past expiration
        $this->paymentMethod->setExpMonth(1);
        $this->paymentMethod->setExpYear(2020);
        $this->assertTrue($this->paymentMethod->isExpired());
    }

    public function testGetDisplayString(): void
    {
        $this->assertEquals('Visa ending in 4242', $this->paymentMethod->getDisplayString());

        $this->paymentMethod->setBrand('mastercard');
        $this->paymentMethod->setLast4('5555');
        $this->assertEquals('Mastercard ending in 5555', $this->paymentMethod->getDisplayString());

        // Non-card type
        $this->paymentMethod->setType(PaymentMethod::TYPE_SEPA_DEBIT);
        $this->paymentMethod->setBrand(null);
        $this->assertEquals('Sepa_debit ending in 5555', $this->paymentMethod->getDisplayString());

        // No last4
        $this->paymentMethod->setLast4(null);
        $this->assertEquals('Sepa_debit', $this->paymentMethod->getDisplayString());
    }

    public function testToArray(): void
    {
        $address = new Address('Boston', 'US');
        $this->paymentMethod->setExpMonth(12);
        $this->paymentMethod->setExpYear(2025);
        $this->paymentMethod->setFunding('credit');
        $this->paymentMethod->setCountry('US');
        $this->paymentMethod->setBillingAddress($address);
        $this->paymentMethod->setName('Test User');
        $this->paymentMethod->setMetadata(['test' => true]);

        $array = $this->paymentMethod->toArray();

        $this->assertIsArray($array);
        $this->assertEquals('pm_123', $array['id']);
        $this->assertEquals(PaymentMethod::TYPE_CARD, $array['type']);
        $this->assertEquals('cus_123', $array['customerId']);
        $this->assertEquals('visa', $array['brand']);
        $this->assertEquals('4242', $array['last4']);
        $this->assertEquals(12, $array['expMonth']);
        $this->assertEquals(2025, $array['expYear']);
        $this->assertEquals('credit', $array['funding']);
        $this->assertEquals('US', $array['country']);
        $this->assertIsArray($array['billingAddress']);
        $this->assertEquals('Test User', $array['name']);
        $this->assertEquals(['test' => true], $array['metadata']);
    }

    public function testFromArray(): void
    {
        $data = [
            'id' => 'pm_array',
            'type' => PaymentMethod::TYPE_CARD,
            'customerId' => 'cus_array',
            'brand' => 'amex',
            'last4' => '1234',
            'expMonth' => 6,
            'expYear' => 2028,
            'funding' => 'credit',
            'country' => 'GB',
            'billingAddress' => [
                'city' => 'London',
                'country' => 'GB',
            ],
            'name' => 'Array User',
            'email' => 'array@example.com',
            'metadata' => ['source' => 'api'],
            'createdAt' => 1234567890,
        ];

        $pm = PaymentMethod::fromArray($data);

        $this->assertEquals('pm_array', $pm->getId());
        $this->assertEquals(PaymentMethod::TYPE_CARD, $pm->getType());
        $this->assertEquals('cus_array', $pm->getCustomerId());
        $this->assertEquals('amex', $pm->getBrand());
        $this->assertEquals('1234', $pm->getLast4());
        $this->assertEquals(6, $pm->getExpMonth());
        $this->assertEquals(2028, $pm->getExpYear());
        $this->assertEquals('credit', $pm->getFunding());
        $this->assertEquals('GB', $pm->getCountry());
        $this->assertNotNull($pm->getBillingAddress());
        $this->assertEquals('Array User', $pm->getName());
        $this->assertEquals('array@example.com', $pm->getEmail());
    }

    public function testFromArrayWithStripeFormat(): void
    {
        $data = [
            'id' => 'pm_stripe',
            'type' => 'card',
            'customer' => 'cus_stripe',
            'card' => [
                'brand' => 'visa',
                'last4' => '4242',
                'exp_month' => 12,
                'exp_year' => 2025,
                'funding' => 'credit',
                'country' => 'US',
            ],
            'billing_details' => [
                'name' => 'Stripe User',
                'email' => 'stripe@example.com',
                'phone' => '+1234567890',
                'address' => [
                    'city' => 'San Francisco',
                    'country' => 'US',
                ],
            ],
            'created' => 1234567890,
        ];

        $pm = PaymentMethod::fromArray($data);

        $this->assertEquals('pm_stripe', $pm->getId());
        $this->assertEquals('cus_stripe', $pm->getCustomerId());
        $this->assertEquals('visa', $pm->getBrand());
        $this->assertEquals('4242', $pm->getLast4());
        $this->assertEquals(12, $pm->getExpMonth());
        $this->assertEquals(2025, $pm->getExpYear());
        $this->assertEquals('credit', $pm->getFunding());
        $this->assertEquals('US', $pm->getCountry());
        $this->assertEquals('Stripe User', $pm->getName());
        $this->assertEquals('stripe@example.com', $pm->getEmail());
        $this->assertEquals('+1234567890', $pm->getPhone());
        $this->assertNotNull($pm->getBillingAddress());
        $this->assertEquals('San Francisco', $pm->getBillingAddress()->getCity());
    }

    public function testTypeConstants(): void
    {
        $this->assertEquals('card', PaymentMethod::TYPE_CARD);
        $this->assertEquals('bank_account', PaymentMethod::TYPE_BANK_ACCOUNT);
        $this->assertEquals('sepa_debit', PaymentMethod::TYPE_SEPA_DEBIT);
        $this->assertEquals('us_bank_account', PaymentMethod::TYPE_ACH_DEBIT);
        $this->assertEquals('paypal', PaymentMethod::TYPE_PAYPAL);
    }

    public function testFluentInterface(): void
    {
        $result = $this->paymentMethod
            ->setId('pm_fluent')
            ->setBrand('discover')
            ->setLast4('6011')
            ->setExpMonth(3)
            ->setExpYear(2027);

        $this->assertSame($this->paymentMethod, $result);
        $this->assertEquals('pm_fluent', $this->paymentMethod->getId());
    }
}
