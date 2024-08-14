<?php

namespace Utopia\Tests;

use PHPUnit\Framework\TestCase;
use Utopia\Pay\Validator\Stripe\Webhook;

class WebhookTest extends TestCase
{
    public function testValid()
    {
        $header = 't=1723597289,v1=ca18f2c5b48c347b26f2d862f29d93dc1c9c6b319ba2cd934db54333acef1492';
        $secret = getenv('STRIPE_WEBHOOK_SECRET');

        $validator = new Webhook();

        // test valid (Tolerance set to high)
        $isValid = $validator->isValid('{"id": "pi_abcdefg"}', $header, $secret, PHP_INT_MAX);
        $this->assertTrue($isValid);

        // Test time tolerance low
        $isValid = $validator->isValid('{"id": "pi_abcdefg"}', $header, $secret, 10);
        $this->assertFalse($isValid);

        // payload doesn't match
        $isValid = $validator->isValid('{"id": "pi_abcdef"}', $header, $secret, PHP_INT_MAX);
        $this->assertFalse($isValid);

        // Secret doesn't match
        $isValid = $validator->isValid('{"id": "pi_abcdefg"}', $header, $secret.'ef', PHP_INT_MAX);
        $this->assertFalse($isValid);
    }
}
