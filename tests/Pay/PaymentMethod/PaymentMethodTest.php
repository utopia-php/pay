<?php

namespace Utopia\Tests;

use PHPUnit\Framework\TestCase;
use Utopia\Pay\PaymentMethod\PaymentMethod;

class PaymentMethodTest extends TestCase
{
    public function testFromArrayCard(): void
    {
        $method = PaymentMethod::fromArray([
            'id' => 'pm_123',
            'object' => 'payment_method',
            'type' => 'card',
            'customer' => 'cus_123',
            'card' => [
                'brand' => 'visa',
                'last4' => '4242',
                'exp_month' => 8,
                'exp_year' => 2030,
                'funding' => 'credit',
                'country' => 'US',
            ],
            'billing_details' => [
                'name' => 'Jane Doe',
                'email' => 'jane@example.com',
                'address' => [
                    'city' => 'New York',
                    'country' => 'US',
                    'line1' => '123 Main St',
                    'line2' => null,
                    'postal_code' => '10001',
                    'state' => 'NY',
                ],
            ],
            'metadata' => ['source' => 'console'],
            'created' => 1700000000,
        ]);

        $this->assertEquals('pm_123', $method->getId());
        $this->assertTrue($method->isCard());
        $this->assertEquals('cus_123', $method->getCustomerId());
        $this->assertEquals('visa', $method->getBrand());
        $this->assertEquals('4242', $method->getLast4());
        $this->assertEquals(8, $method->getExpMonth());
        $this->assertEquals(2030, $method->getExpYear());
        $this->assertEquals('credit', $method->getFunding());
        $this->assertEquals('US', $method->getCountry());
        $this->assertEquals('Jane Doe', $method->getName());
        $this->assertEquals('jane@example.com', $method->getEmail());
        $this->assertEquals('10001', $method->getBillingAddress()?->getPostalCode());
        $this->assertEquals(['source' => 'console'], $method->getMetadata());
        $this->assertEquals(1700000000, $method->getCreatedAt());
    }

    public function testFromArrayNonCardAndEmptyAddress(): void
    {
        $method = PaymentMethod::fromArray([
            'id' => 'pm_456',
            'type' => 'sepa_debit',
            'customer' => null,
            'sepa_debit' => ['last4' => '3000', 'country' => 'DE'],
            'billing_details' => [
                'address' => ['city' => null, 'country' => null, 'line1' => null, 'line2' => null, 'postal_code' => null, 'state' => null],
            ],
        ]);

        $this->assertFalse($method->isCard());
        $this->assertEquals('3000', $method->getLast4());
        $this->assertEquals('DE', $method->getCountry());
        $this->assertNull($method->getBrand());
        $this->assertNull($method->getCustomerId());
        $this->assertNull($method->getBillingAddress());
        $this->assertFalse($method->isExpired());
    }

    public function testIsExpired(): void
    {
        $method = new PaymentMethod('pm_123', PaymentMethod::TYPE_CARD, expMonth: 8, expYear: 2030);

        $this->assertFalse($method->isExpired(new \DateTimeImmutable('2030-08-31')));
        $this->assertTrue($method->isExpired(new \DateTimeImmutable('2030-09-01')));
    }
}
