<?php

namespace Utopia\Tests;

use PHPUnit\Framework\TestCase;
use Utopia\Pay\Customer\Customer;

class CustomerTest extends TestCase
{
    public function testFromArray(): void
    {
        $customer = Customer::fromArray([
            'id' => 'cus_Qabc',
            'object' => 'customer',
            'address' => ['city' => 'Kathmandu', 'country' => 'NP', 'line1' => 'Gaurighat', 'line2' => null, 'postal_code' => '44600', 'state' => 'Bagmati'],
            'created' => 1726000000,
            'email' => 'jane@example.com',
            'invoice_settings' => ['default_payment_method' => 'pm_1Q0abc'],
            'metadata' => ['userId' => 'user_1'],
            'name' => 'Jane Doe',
            'phone' => null,
        ]);

        $this->assertEquals('cus_Qabc', $customer->getId());
        $this->assertEquals('Jane Doe', $customer->getName());
        $this->assertEquals('jane@example.com', $customer->getEmail());
        $this->assertNull($customer->getPhone());
        $this->assertEquals('pm_1Q0abc', $customer->getDefaultPaymentMethodId());
        $this->assertEquals(['userId' => 'user_1'], $customer->getMetadata());
        $this->assertEquals(1726000000, $customer->getCreatedAt());
        $this->assertEquals('Kathmandu', $customer->getRaw()['address']['city']);
        $this->assertFalse($customer->isDeleted());
    }

    public function testFromArrayDeleted(): void
    {
        $customer = Customer::fromArray(['id' => 'cus_Qabc', 'object' => 'customer', 'deleted' => true]);

        $this->assertEquals('cus_Qabc', $customer->getId());
        $this->assertNull($customer->getName());
        $this->assertNull($customer->getDefaultPaymentMethodId());
        $this->assertTrue($customer->isDeleted());
    }
}
