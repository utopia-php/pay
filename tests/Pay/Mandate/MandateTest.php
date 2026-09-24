<?php

namespace Utopia\Tests;

use PHPUnit\Framework\TestCase;
use Utopia\Pay\Mandate\Mandate;

class MandateTest extends TestCase
{
    public function testFromArray(): void
    {
        $mandate = Mandate::fromArray([
            'id' => 'mandate_1Q0abc',
            'object' => 'mandate',
            'customer_acceptance' => ['type' => 'online', 'accepted_at' => 1726000000],
            'payment_method' => 'pm_1Q0abc',
            'status' => 'active',
            'type' => 'multi_use',
        ]);

        $this->assertEquals('mandate_1Q0abc', $mandate->getId());
        $this->assertEquals('active', $mandate->getStatus());
        $this->assertEquals('pm_1Q0abc', $mandate->getPaymentMethodId());
        $this->assertTrue($mandate->isActive());
    }

    public function testFromArrayWithExpandedPaymentMethod(): void
    {
        $mandate = Mandate::fromArray([
            'id' => 'mandate_123',
            'status' => 'inactive',
            'payment_method' => ['id' => 'pm_123', 'object' => 'payment_method'],
        ]);

        $this->assertEquals('pm_123', $mandate->getPaymentMethodId());
        $this->assertFalse($mandate->isActive());
    }

    public function testFromArrayWithoutStatus(): void
    {
        $mandate = Mandate::fromArray(['id' => 'mandate_123']);

        $this->assertNull($mandate->getStatus());
        $this->assertFalse($mandate->isActive());
    }
}
