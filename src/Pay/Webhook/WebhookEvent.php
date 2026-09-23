<?php

namespace Utopia\Pay\Webhook;

/**
 * Typed view of a verified webhook event, e.g. WebhookEvent::fromArray($pay->constructWebhookEvent(...)).
 */
class WebhookEvent
{
    public const TYPE_CHARGE_DISPUTE_CREATED = 'charge.dispute.created';

    /**
     * @param  array<string, mixed>  $object
     */
    public function __construct(
        private string $id,
        private string $type,
        private array $object = [],
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    /**
     * The resource the event is about (`data.object`), e.g. a payment intent or dispute payload
     *
     * @return array<string, mixed>
     */
    public function getObject(): array
    {
        return $this->object;
    }

    /**
     * Kind of resource in getObject(), e.g. `payment_intent`, `mandate` or `dispute`
     */
    public function getObjectType(): string
    {
        return (string) ($this->object['object'] ?? '');
    }

    /**
     * @param  array<string, mixed>  $data  Event payload
     */
    public static function fromArray(array $data): self
    {
        $object = $data['data']['object'] ?? [];

        return new self(
            id: (string) ($data['id'] ?? ''),
            type: (string) ($data['type'] ?? ''),
            object: is_array($object) ? $object : [],
        );
    }
}
