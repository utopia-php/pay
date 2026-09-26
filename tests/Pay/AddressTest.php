<?php

namespace Utopia\Tests;

use PHPUnit\Framework\TestCase;
use Utopia\Pay\Address;

class AddressTest extends TestCase
{
    public function testFromArrayRoundTrip(): void
    {
        $address = new Address('New York', 'US', '123 Main St', 'Apt 4B', '10001', 'NY');

        $this->assertEquals($address->asArray(), Address::fromArray($address->asArray())->asArray());
    }

    public function testFromArrayWithPartialData(): void
    {
        $address = Address::fromArray(['country' => 'NP', 'city' => null]);

        $this->assertEquals('', $address->getCity());
        $this->assertEquals('NP', $address->getCountry());
        $this->assertNull($address->getLine1());
        $this->assertNull($address->getPostalCode());
    }
}
