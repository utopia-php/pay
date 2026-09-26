<?php

namespace Utopia\Tests;

use PHPUnit\Framework\TestCase;
use Utopia\Pay\Charge\Charge;

class ChargeTest extends TestCase
{
    public function testFromArray(): void
    {
        $charge = Charge::fromArray([
            'id' => 'ch_3Q0abc',
            'object' => 'charge',
            'amount' => 2500,
            'amount_refunded' => 2500,
            'currency' => 'usd',
            'refunded' => true,
            'status' => 'succeeded',
        ]);

        $this->assertEquals('ch_3Q0abc', $charge->getId());
        $this->assertEquals(2500, $charge->getAmount());
        $this->assertEquals(2500, $charge->getAmountRefunded());
        $this->assertEquals('usd', $charge->getCurrency());
        $this->assertEquals('succeeded', $charge->getStatus());
        $this->assertTrue($charge->isRefunded());
    }

    public function testFromArrayEmpty(): void
    {
        $charge = Charge::fromArray([]);

        $this->assertNull($charge->getAmountRefunded());
        $this->assertFalse($charge->isRefunded());
    }
}
