<?php

namespace Utopia\Tests;

use PHPUnit\Framework\TestCase;
use Utopia\Pay\Address;
use Utopia\Pay\Customer\Customer;

class CustomerTest extends TestCase
{
    private Customer $customer;

    private string $customerId = 'cus_123';

    private string $name = 'John Doe';

    private string $email = 'john@example.com';

    protected function setUp(): void
    {
        $this->customer = new Customer(
            $this->customerId,
            $this->name,
            $this->email
        );
    }

    public function testConstructor(): void
    {
        $this->assertEquals($this->customerId, $this->customer->getId());
        $this->assertEquals($this->name, $this->customer->getName());
        $this->assertEquals($this->email, $this->customer->getEmail());
        $this->assertNull($this->customer->getAddress());
        $this->assertNull($this->customer->getPhone());
        $this->assertNull($this->customer->getDefaultPaymentMethod());
        $this->assertEmpty($this->customer->getMetadata());
        $this->assertNotNull($this->customer->getCreatedAt());
    }

    public function testConstructorWithAllParameters(): void
    {
        $address = new Address('New York', 'US', '123 Main St');
        $customer = new Customer(
            'cus_456',
            'Jane Doe',
            'jane@example.com',
            $address,
            '+1234567890',
            'pm_123',
            ['key' => 'value'],
            1234567890
        );

        $this->assertEquals('cus_456', $customer->getId());
        $this->assertEquals('Jane Doe', $customer->getName());
        $this->assertEquals('jane@example.com', $customer->getEmail());
        $this->assertSame($address, $customer->getAddress());
        $this->assertEquals('+1234567890', $customer->getPhone());
        $this->assertEquals('pm_123', $customer->getDefaultPaymentMethod());
        $this->assertEquals(['key' => 'value'], $customer->getMetadata());
        $this->assertEquals(1234567890, $customer->getCreatedAt());
    }

    public function testGettersAndSetters(): void
    {
        $address = new Address('Los Angeles', 'US');

        $this->customer->setId('cus_new');
        $this->customer->setName('New Name');
        $this->customer->setEmail('new@example.com');
        $this->customer->setAddress($address);
        $this->customer->setPhone('+9876543210');
        $this->customer->setDefaultPaymentMethod('pm_456');
        $this->customer->setMetadata(['foo' => 'bar']);
        $this->customer->setCreatedAt(9876543210);

        $this->assertEquals('cus_new', $this->customer->getId());
        $this->assertEquals('New Name', $this->customer->getName());
        $this->assertEquals('new@example.com', $this->customer->getEmail());
        $this->assertSame($address, $this->customer->getAddress());
        $this->assertEquals('+9876543210', $this->customer->getPhone());
        $this->assertEquals('pm_456', $this->customer->getDefaultPaymentMethod());
        $this->assertEquals(['foo' => 'bar'], $this->customer->getMetadata());
        $this->assertEquals(9876543210, $this->customer->getCreatedAt());
    }

    public function testHasAddress(): void
    {
        $this->assertFalse($this->customer->hasAddress());

        $this->customer->setAddress(new Address('Chicago', 'US'));
        $this->assertTrue($this->customer->hasAddress());

        $this->customer->setAddress(null);
        $this->assertFalse($this->customer->hasAddress());
    }

    public function testHasDefaultPaymentMethod(): void
    {
        $this->assertFalse($this->customer->hasDefaultPaymentMethod());

        $this->customer->setDefaultPaymentMethod('pm_123');
        $this->assertTrue($this->customer->hasDefaultPaymentMethod());

        $this->customer->setDefaultPaymentMethod(null);
        $this->assertFalse($this->customer->hasDefaultPaymentMethod());
    }

    public function testToArray(): void
    {
        $address = new Address('Boston', 'US', '456 Oak Ave');
        $this->customer->setAddress($address);
        $this->customer->setPhone('+1112223333');
        $this->customer->setDefaultPaymentMethod('pm_789');
        $this->customer->setMetadata(['tier' => 'premium']);
        $this->customer->setCreatedAt(1234567890);

        $array = $this->customer->toArray();

        $this->assertIsArray($array);
        $this->assertEquals($this->customerId, $array['id']);
        $this->assertEquals($this->name, $array['name']);
        $this->assertEquals($this->email, $array['email']);
        $this->assertIsArray($array['address']);
        $this->assertEquals('+1112223333', $array['phone']);
        $this->assertEquals('pm_789', $array['defaultPaymentMethod']);
        $this->assertEquals(['tier' => 'premium'], $array['metadata']);
        $this->assertEquals(1234567890, $array['createdAt']);
    }

    public function testToArrayWithNullAddress(): void
    {
        $array = $this->customer->toArray();

        $this->assertNull($array['address']);
    }

    public function testFromArray(): void
    {
        $data = [
            'id' => 'cus_array',
            'name' => 'Array Customer',
            'email' => 'array@example.com',
            'address' => [
                'city' => 'Seattle',
                'country' => 'US',
                'line1' => '789 Pine St',
            ],
            'phone' => '+4445556666',
            'defaultPaymentMethod' => 'pm_array',
            'metadata' => ['source' => 'web'],
            'createdAt' => 9876543210,
        ];

        $customer = Customer::fromArray($data);

        $this->assertEquals('cus_array', $customer->getId());
        $this->assertEquals('Array Customer', $customer->getName());
        $this->assertEquals('array@example.com', $customer->getEmail());
        $this->assertNotNull($customer->getAddress());
        $this->assertEquals('Seattle', $customer->getAddress()->getCity());
        $this->assertEquals('+4445556666', $customer->getPhone());
        $this->assertEquals('pm_array', $customer->getDefaultPaymentMethod());
        $this->assertEquals(['source' => 'web'], $customer->getMetadata());
        $this->assertEquals(9876543210, $customer->getCreatedAt());
    }

    public function testFromArrayWithMinimalData(): void
    {
        $data = [
            'id' => 'cus_minimal',
        ];

        $customer = Customer::fromArray($data);

        $this->assertEquals('cus_minimal', $customer->getId());
        $this->assertEquals('', $customer->getName());
        $this->assertEquals('', $customer->getEmail());
        $this->assertNull($customer->getAddress());
    }

    public function testFromArrayWithStripeFormat(): void
    {
        $data = [
            'id' => 'cus_stripe',
            'name' => 'Stripe Customer',
            'email' => 'stripe@example.com',
            'default_payment_method' => 'pm_stripe',
            'created' => 1234567890,
        ];

        $customer = Customer::fromArray($data);

        $this->assertEquals('cus_stripe', $customer->getId());
        $this->assertEquals('pm_stripe', $customer->getDefaultPaymentMethod());
        $this->assertEquals(1234567890, $customer->getCreatedAt());
    }

    public function testFluentInterface(): void
    {
        $result = $this->customer
            ->setId('cus_fluent')
            ->setName('Fluent')
            ->setEmail('fluent@example.com')
            ->setPhone('+1111111111')
            ->setMetadata(['test' => true]);

        $this->assertSame($this->customer, $result);
        $this->assertEquals('cus_fluent', $this->customer->getId());
    }
}
