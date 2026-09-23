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

    public function testFromArrayWithoutObject(): void
    {
        $event = WebhookEvent::fromArray(['id' => 'evt_123', 'type' => 'ping']);

        $this->assertEquals([], $event->getObject());
        $this->assertEquals('', $event->getObjectType());
    }
}
