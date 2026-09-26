<?php

namespace Utopia\Tests;

use PHPUnit\Framework\TestCase;
use Utopia\Pay\Refund\Refund;

class RefundTest extends TestCase
{
    public function testFromArray(): void
    {
        $refund = Refund::fromArray([
            'id' => 're_3Q0abc',
            'object' => 'refund',
            'amount' => 3000,
            'charge' => 'ch_3Q0abc',
            'created' => 1726000000,
            'currency' => 'usd',
            'metadata' => [],
            'payment_intent' => 'pi_3Q0abc',
            'reason' => 'requested_by_customer',
            'status' => 'succeeded',
        ]);

        $this->assertEquals('re_3Q0abc', $refund->getId());
        $this->assertEquals(3000, $refund->getAmount());
        $this->assertEquals('usd', $refund->getCurrency());
        $this->assertEquals('succeeded', $refund->getStatus());
        $this->assertEquals('pi_3Q0abc', $refund->getPaymentIntentId());
        $this->assertEquals('ch_3Q0abc', $refund->getChargeId());
        $this->assertEquals('requested_by_customer', $refund->getReason());
        $this->assertNull($refund->getFailureReason());
        $this->assertEquals(1726000000, $refund->getCreatedAt());
        $this->assertTrue($refund->isSucceeded());
    }

    public function testFromArrayFailed(): void
    {
        $refund = Refund::fromArray([
            'id' => 're_123',
            'status' => 'failed',
            'failure_reason' => 'expired_or_canceled_card',
            'payment_intent' => ['id' => 'pi_123', 'object' => 'payment_intent'],
        ]);

        $this->assertEquals('pi_123', $refund->getPaymentIntentId());
        $this->assertEquals('expired_or_canceled_card', $refund->getFailureReason());
        $this->assertFalse($refund->isSucceeded());
    }
}
