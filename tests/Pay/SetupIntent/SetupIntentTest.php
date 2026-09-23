<?php

namespace Utopia\Tests;

use PHPUnit\Framework\TestCase;
use Utopia\Pay\SetupIntent\SetupIntent;

class SetupIntentTest extends TestCase
{
    public function testFromArray(): void
    {
        $intent = SetupIntent::fromArray([
            'id' => 'seti_123',
            'object' => 'setup_intent',
            'status' => 'succeeded',
            'customer' => 'cus_123',
            'payment_method' => ['id' => 'pm_123', 'object' => 'payment_method'],
            'client_secret' => 'seti_123_secret_abc',
            'mandate' => 'mandate_123',
        ]);

        $this->assertEquals('seti_123', $intent->getId());
        $this->assertTrue($intent->isSucceeded());
        $this->assertEquals('cus_123', $intent->getCustomerId());
        $this->assertEquals('pm_123', $intent->getPaymentMethodId());
        $this->assertEquals('seti_123_secret_abc', $intent->getClientSecret());
        $this->assertEquals('mandate_123', $intent->getMandateId());
    }

    public function testFromArrayPending(): void
    {
        $intent = SetupIntent::fromArray(['id' => 'seti_123', 'status' => 'requires_payment_method', 'payment_method' => null]);

        $this->assertFalse($intent->isSucceeded());
        $this->assertNull($intent->getPaymentMethodId());
        $this->assertNull($intent->getMandateId());
    }
}
