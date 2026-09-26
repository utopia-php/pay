<?php

namespace Utopia\Tests;

use PHPUnit\Framework\TestCase;
use Utopia\Pay\Validator\Stripe\Webhook;

class WebhookTest extends TestCase
{
    private const SECRET = 'whsec_test';

    private const PAYLOAD = '{"id": "evt_123"}';

    private function sign(int $timestamp, string $payload = self::PAYLOAD, string $secret = self::SECRET): string
    {
        return 't='.$timestamp.',v1='.hash_hmac('sha256', $timestamp.'.'.$payload, $secret);
    }

    public function testValid(): void
    {
        $this->assertTrue((new Webhook())->isValid(self::PAYLOAD, $this->sign(time()), self::SECRET, Webhook::DEFAULT_TOLERANCE));
    }

    public function testAnyMatchingSignatureIsAccepted(): void
    {
        // Stripe sends one v1 entry per active secret while a secret is being rolled
        $header = $this->sign(time()).',v1='.str_repeat('0', 64).',v0=legacy';

        $this->assertTrue((new Webhook())->isValid(self::PAYLOAD, $header, self::SECRET, Webhook::DEFAULT_TOLERANCE));
    }

    public function testStaleTimestamp(): void
    {
        $header = $this->sign(time() - Webhook::DEFAULT_TOLERANCE - 1);

        $this->assertFalse((new Webhook())->isValid(self::PAYLOAD, $header, self::SECRET, Webhook::DEFAULT_TOLERANCE));
        $this->assertTrue((new Webhook())->isValid(self::PAYLOAD, $header, self::SECRET, null));
    }

    public function testBadSignature(): void
    {
        $validator = new Webhook();

        $this->assertFalse($validator->isValid('{"id": "evt_124"}', $this->sign(time()), self::SECRET, Webhook::DEFAULT_TOLERANCE));
        $this->assertFalse($validator->isValid(self::PAYLOAD, $this->sign(time()), self::SECRET.'x', Webhook::DEFAULT_TOLERANCE));
    }

    public function testMalformedHeader(): void
    {
        $validator = new Webhook();

        $this->assertFalse($validator->isValid(self::PAYLOAD, '', self::SECRET));
        $this->assertFalse($validator->isValid(self::PAYLOAD, 't,v1', self::SECRET));
        $this->assertFalse($validator->isValid(self::PAYLOAD, 't=abc,v1=def', self::SECRET));
        $this->assertFalse($validator->isValid(self::PAYLOAD, 't='.time(), self::SECRET));
    }
}
