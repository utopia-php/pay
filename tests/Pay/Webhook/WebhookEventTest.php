<?php

namespace Utopia\Tests;

use PHPUnit\Framework\TestCase;
use Utopia\Pay\Webhook\WebhookEvent;

class WebhookEventTest extends TestCase
{
    public function testFromArray(): void
    {
        $event = WebhookEvent::fromArray([
            'id' => 'evt_123',
            'object' => 'event',
            'type' => 'charge.dispute.created',
            'data' => ['object' => ['id' => 'dp_123', 'object' => 'dispute']],
        ]);

        $this->assertEquals('evt_123', $event->getId());
        $this->assertEquals(WebhookEvent::TYPE_CHARGE_DISPUTE_CREATED, $event->getType());
        $this->assertEquals('dispute', $event->getObjectType());
        $this->assertEquals('dp_123', $event->getObject()['id']);
    }

    public function testFromArrayEventTypes(): void
    {
        $succeeded = WebhookEvent::fromArray([
            'id' => 'evt_1',
            'type' => 'payment_intent.succeeded',
            'data' => ['object' => ['id' => 'pi_123', 'object' => 'payment_intent']],
        ]);
        $mandate = WebhookEvent::fromArray([
            'id' => 'evt_2',
            'type' => 'mandate.updated',
            'data' => ['object' => ['id' => 'mandate_123', 'object' => 'mandate']],
        ]);

        $this->assertEquals(WebhookEvent::TYPE_PAYMENT_INTENT_SUCCEEDED, $succeeded->getType());
        $this->assertEquals('payment_intent', $succeeded->getObjectType());
        $this->assertEquals(WebhookEvent::TYPE_MANDATE_UPDATED, $mandate->getType());
        $this->assertEquals('mandate', $mandate->getObjectType());
    }

    public function testFromArrayWithoutObject(): void
    {
        $event = WebhookEvent::fromArray(['id' => 'evt_123', 'type' => 'ping']);

        $this->assertEquals([], $event->getObject());
        $this->assertEquals('', $event->getObjectType());
    }
}
