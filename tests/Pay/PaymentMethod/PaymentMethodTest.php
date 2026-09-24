<?php

namespace Utopia\Tests;

use PHPUnit\Framework\TestCase;
use Utopia\Pay\PaymentMethod\PaymentMethod;

class PaymentMethodTest extends TestCase
{
    public function testFromArray(): void
    {
        $method = PaymentMethod::fromArray([
            'id' => 'pm_1Q0abc',
            'object' => 'payment_method',
            'billing_details' => [
                'address' => [
                    'city' => 'New York',
                    'country' => 'US',
                    'line1' => '1 Main St',
                    'line2' => null,
                    'postal_code' => '10001',
                    'state' => 'NY',
                ],
                'email' => 'jane@example.com',
                'name' => 'Jane Doe',
                'phone' => null,
            ],
            'card' => [
                'brand' => 'visa',
                'country' => 'US',
                'exp_month' => 8,
                'exp_year' => 2030,
                'funding' => 'credit',
                'last4' => '4242',
            ],
            'created' => 1726000000,
            'customer' => 'cus_Qabc',
            'metadata' => [],
            'type' => 'card',
        ]);

        $this->assertEquals('pm_1Q0abc', $method->getId());
        $this->assertEquals('card', $method->getType());
        $this->assertEquals('cus_Qabc', $method->getCustomerId());
        $this->assertEquals('visa', $method->getBrand());
        $this->assertEquals('4242', $method->getLast4());
        $this->assertEquals(8, $method->getExpMonth());
        $this->assertEquals(2030, $method->getExpYear());
        $this->assertEquals('credit', $method->getFunding());
        $this->assertEquals('US', $method->getCountry());
        $this->assertEquals('Jane Doe', $method->getBillingName());
        $this->assertEquals('jane@example.com', $method->getBillingEmail());
        $this->assertNull($method->getBillingPhone());
        $this->assertEquals('New York', $method->getBillingCity());
        $this->assertEquals('US', $method->getBillingCountry());
        $this->assertEquals('1 Main St', $method->getBillingLine1());
        $this->assertNull($method->getBillingLine2());
        $this->assertEquals('10001', $method->getBillingPostalCode());
        $this->assertEquals('NY', $method->getBillingState());
        $this->assertEquals(1726000000, $method->getCreatedAt());
        $this->assertTrue($method->isCard());
    }

    public function testFromArrayWithoutCardOrAddress(): void
    {
        $method = PaymentMethod::fromArray([
            'id' => 'pm_123',
            'type' => 'sepa_debit',
            'billing_details' => ['address' => ['city' => null, 'country' => null, 'line1' => null, 'line2' => null, 'postal_code' => null, 'state' => null]],
            'customer' => ['id' => 'cus_123', 'object' => 'customer'],
        ]);

        $this->assertEquals('cus_123', $method->getCustomerId());
        $this->assertNull($method->getBrand());
        $this->assertNull($method->getExpMonth());
        $this->assertNull($method->getBillingCity());
        $this->assertNull($method->getBillingPostalCode());
        $this->assertFalse($method->isCard());
        $this->assertFalse($method->isExpired());
    }

    public function testIsExpired(): void
    {
        $method = PaymentMethod::fromArray(['type' => 'card', 'card' => ['exp_month' => 3, 'exp_year' => 2026]]);

        $this->assertFalse($method->isExpired(new \DateTimeImmutable('2026-03-31')));
        $this->assertTrue($method->isExpired(new \DateTimeImmutable('2026-04-01')));
    }
}
