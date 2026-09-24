<?php

namespace Utopia\Tests;

use PHPUnit\Framework\TestCase;
use Utopia\Pay\SetupIntent\SetupIntent;

class SetupIntentTest extends TestCase
{
    public function testFromArray(): void
    {
        $intent = SetupIntent::fromArray([
            'id' => 'seti_1Q0abc',
            'object' => 'setup_intent',
            'client_secret' => 'seti_1Q0abc_secret_xyz',
            'customer' => 'cus_Qabc',
            'mandate' => 'mandate_1Q0abc',
            'payment_method' => 'pm_1Q0abc',
            'payment_method_options' => [
                'card' => ['mandate_options' => ['reference' => 'user_1', 'interval' => 'sporadic'], 'request_three_d_secure' => 'automatic'],
            ],
            'status' => 'succeeded',
            'usage' => 'off_session',
        ]);

        $this->assertEquals('seti_1Q0abc', $intent->getId());
        $this->assertEquals('succeeded', $intent->getStatus());
        $this->assertEquals('cus_Qabc', $intent->getCustomerId());
        $this->assertEquals('pm_1Q0abc', $intent->getPaymentMethodId());
        $this->assertEquals('seti_1Q0abc_secret_xyz', $intent->getClientSecret());
        $this->assertEquals('mandate_1Q0abc', $intent->getMandateId());
        $this->assertEquals('user_1', $intent->getPaymentMethodOptions()['card']['mandate_options']['reference'] ?? null);
        $this->assertTrue($intent->isSucceeded());
    }

    public function testFromArrayPending(): void
    {
        $intent = SetupIntent::fromArray([
            'id' => 'seti_123',
            'status' => 'requires_payment_method',
            'payment_method' => null,
            'mandate' => null,
        ]);

        $this->assertNull($intent->getPaymentMethodId());
        $this->assertNull($intent->getMandateId());
        $this->assertNull($intent->getClientSecret());
        $this->assertFalse($intent->isSucceeded());
    }
}
