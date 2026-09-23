<?php

namespace Utopia\Tests;

use PHPUnit\Framework\TestCase;
use Utopia\Pay\Adapter\Stripe;
use Utopia\Pay\Exception;
use Utopia\Pay\Pay;
use Utopia\Pay\Webhook\WebhookEvent;

class StripeWebhookTest extends TestCase
{
    private const SECRET = 'whsec_test';

    private Pay $pay;

    protected function setUp(): void
    {
        $this->pay = new Pay(new Stripe('sk_test'));
    }

    private function sign(string $payload, int $timestamp): string
    {
        return 't='.$timestamp.',v1='.hash_hmac('sha256', $timestamp.'.'.$payload, self::SECRET);
    }

    public function testConstructWebhookEvent(): void
    {
        $payload = (string) json_encode([
            'id' => 'evt_123',
            'type' => WebhookEvent::TYPE_CHARGE_DISPUTE_CREATED,
            'data' => ['object' => ['id' => 'dp_123', 'object' => 'dispute']],
        ]);

        $event = $this->pay->constructWebhookEvent($payload, $this->sign($payload, time()), self::SECRET);

        $this->assertEquals('evt_123', $event['id']);
        $this->assertEquals('dp_123', WebhookEvent::fromArray($event)->getObject()['id']);
    }

    public function testStaleTimestamp(): void
    {
        $payload = '{"id":"evt_123"}';
        $header = $this->sign($payload, time() - 301);

        $this->assertEquals('evt_123', $this->pay->constructWebhookEvent($payload, $header, self::SECRET, null)['id']);

        $this->expectException(Exception::class);
        $this->pay->constructWebhookEvent($payload, $header, self::SECRET);
    }

    public function testBadSignature(): void
    {
        try {
            $this->pay->constructWebhookEvent('{"id":"evt_123"}', $this->sign('{"id":"evt_124"}', time()), self::SECRET);
            $this->fail('Expected exception');
        } catch (Exception $e) {
            $this->assertEquals(Exception::SIGNATURE_VERIFICATION_FAILED, $e->getType());
            $this->assertEquals(400, $e->getCode());
        }
    }

    public function testSignedButInvalidJson(): void
    {
        $this->expectException(Exception::class);
        $this->pay->constructWebhookEvent('not json', $this->sign('not json', time()), self::SECRET);
    }
}
