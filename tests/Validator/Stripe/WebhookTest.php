<?php

declare(strict_types=1);

namespace Utopia\Pay\Tests\Validator\Stripe;

use PHPUnit\Framework\TestCase;
use Utopia\Pay\Validator\Stripe\Webhook;

class WebhookTest extends TestCase
{
    public function testValid(): void
    {
        // Signed here with a throwaway secret, as Stripe signs: HMAC-SHA256 of "{t}.{payload}".
        $secret = 'whsec_test';
        $timestamp = 1723597289;
        $header = "t={$timestamp},v1=" . \hash_hmac('sha256', "{$timestamp}." . '{"id": "pi_abcdefg"}', $secret);

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
