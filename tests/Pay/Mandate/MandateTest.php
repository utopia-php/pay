<?php

namespace Utopia\Tests;

use PHPUnit\Framework\TestCase;
use Utopia\Pay\Mandate\Mandate;

class MandateTest extends TestCase
{
    public function testFromArray(): void
    {
        $mandate = Mandate::fromArray([
            'id' => 'mandate_123',
            'object' => 'mandate',
            'status' => 'active',
            'payment_method' => 'pm_123',
            'type' => 'multi_use',
        ]);

        $this->assertEquals('mandate_123', $mandate->getId());
        $this->assertEquals('pm_123', $mandate->getPaymentMethodId());
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
        $this->assertEquals('inactive', $mandate->getStatus());
        $this->assertFalse($mandate->isActive());
    }
}
