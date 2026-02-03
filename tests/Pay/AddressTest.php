<?php

namespace Utopia\Tests;

use PHPUnit\Framework\TestCase;
use Utopia\Pay\Address;

class AddressTest extends TestCase
{
    private Address $address;

    protected function setUp(): void
    {
        $this->address = new Address(
            'New York',
            'US',
            '123 Main St',
            'Apt 4B',
            '10001',
            'NY'
        );
    }

    public function testConstructor(): void
    {
        $this->assertEquals('New York', $this->address->getCity());
        $this->assertEquals('US', $this->address->getCountry());
        $this->assertEquals('123 Main St', $this->address->getLine1());
        $this->assertEquals('Apt 4B', $this->address->getLine2());
        $this->assertEquals('10001', $this->address->getPostalCode());
        $this->assertEquals('NY', $this->address->getState());
    }

    public function testConstructorWithMinimalParameters(): void
    {
        $address = new Address('London', 'GB');

        $this->assertEquals('London', $address->getCity());
        $this->assertEquals('GB', $address->getCountry());
        $this->assertNull($address->getLine1());
        $this->assertNull($address->getLine2());
        $this->assertNull($address->getPostalCode());
        $this->assertNull($address->getState());
    }

    public function testGettersAndSetters(): void
    {
        $this->address->setCity('Los Angeles');
        $this->address->setCountry('CA');
        $this->address->setLine1('456 Oak Ave');
        $this->address->setLine2('Suite 100');
        $this->address->setPostalCode('90001');
        $this->address->setState('California');

        $this->assertEquals('Los Angeles', $this->address->getCity());
        $this->assertEquals('CA', $this->address->getCountry());
        $this->assertEquals('456 Oak Ave', $this->address->getLine1());
        $this->assertEquals('Suite 100', $this->address->getLine2());
        $this->assertEquals('90001', $this->address->getPostalCode());
        $this->assertEquals('California', $this->address->getState());
    }

    public function testAsArray(): void
    {
        $array = $this->address->asArray();

        $this->assertIsArray($array);
        $this->assertEquals('New York', $array['city']);
        $this->assertEquals('US', $array['country']);
        $this->assertEquals('123 Main St', $array['line1']);
        $this->assertEquals('Apt 4B', $array['line2']);
        $this->assertEquals('10001', $array['postal_code']); // Note: snake_case
        $this->assertEquals('NY', $array['state']);
    }

    public function testToArray(): void
    {
        $array = $this->address->toArray();

        $this->assertIsArray($array);
        $this->assertEquals('New York', $array['city']);
        $this->assertEquals('US', $array['country']);
        $this->assertEquals('123 Main St', $array['line1']);
        $this->assertEquals('Apt 4B', $array['line2']);
        $this->assertEquals('10001', $array['postalCode']); // Note: camelCase
        $this->assertEquals('NY', $array['state']);
    }

    public function testFromArray(): void
    {
        $data = [
            'city' => 'Chicago',
            'country' => 'US',
            'line1' => '789 Pine St',
            'line2' => null,
            'postalCode' => '60601',
            'state' => 'IL',
        ];

        $address = Address::fromArray($data);

        $this->assertEquals('Chicago', $address->getCity());
        $this->assertEquals('US', $address->getCountry());
        $this->assertEquals('789 Pine St', $address->getLine1());
        $this->assertNull($address->getLine2());
        $this->assertEquals('60601', $address->getPostalCode());
        $this->assertEquals('IL', $address->getState());
    }

    public function testFromArrayWithSnakeCasePostalCode(): void
    {
        $data = [
            'city' => 'Boston',
            'country' => 'US',
            'postal_code' => '02101', // snake_case
        ];

        $address = Address::fromArray($data);

        $this->assertEquals('Boston', $address->getCity());
        $this->assertEquals('02101', $address->getPostalCode());
    }

    public function testFromArrayWithMinimalData(): void
    {
        $data = [
            'city' => 'Seattle',
            'country' => 'US',
        ];

        $address = Address::fromArray($data);

        $this->assertEquals('Seattle', $address->getCity());
        $this->assertEquals('US', $address->getCountry());
        $this->assertNull($address->getLine1());
        $this->assertNull($address->getPostalCode());
    }

    public function testFromArrayWithEmptyData(): void
    {
        $address = Address::fromArray([]);

        $this->assertEquals('', $address->getCity());
        $this->assertEquals('', $address->getCountry());
    }

    public function testIsComplete(): void
    {
        $this->assertTrue($this->address->isComplete());

        $incompleteAddress = new Address('', 'US');
        $this->assertFalse($incompleteAddress->isComplete());

        $incompleteAddress2 = new Address('New York', '');
        $this->assertFalse($incompleteAddress2->isComplete());
    }

    public function testIsEmpty(): void
    {
        $this->assertFalse($this->address->isEmpty());

        $emptyAddress = new Address('', '');
        $this->assertTrue($emptyAddress->isEmpty());

        // Address with only city is not empty
        $partialAddress = new Address('New York', '');
        $this->assertFalse($partialAddress->isEmpty());
    }

    public function testFluentInterface(): void
    {
        $result = $this->address
            ->setCity('Miami')
            ->setCountry('US')
            ->setLine1('100 Beach Blvd')
            ->setState('FL');

        $this->assertSame($this->address, $result);
        $this->assertEquals('Miami', $this->address->getCity());
    }

    public function testRoundTripConversion(): void
    {
        $array = $this->address->toArray();
        $newAddress = Address::fromArray($array);

        $this->assertEquals($this->address->getCity(), $newAddress->getCity());
        $this->assertEquals($this->address->getCountry(), $newAddress->getCountry());
        $this->assertEquals($this->address->getLine1(), $newAddress->getLine1());
        $this->assertEquals($this->address->getLine2(), $newAddress->getLine2());
        $this->assertEquals($this->address->getPostalCode(), $newAddress->getPostalCode());
        $this->assertEquals($this->address->getState(), $newAddress->getState());
    }
}
