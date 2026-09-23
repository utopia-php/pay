<?php

namespace Utopia\Pay\Webhook;

/**
 * Typed view of a verified webhook event, e.g. WebhookEvent::fromArray($pay->constructWebhookEvent(...)).
 */
class WebhookEvent
{
    public const TYPE_PAYMENT_INTENT_SUCCEEDED = 'payment_intent.succeeded';

    public const TYPE_PAYMENT_INTENT_PAYMENT_FAILED = 'payment_intent.payment_failed';

    public const TYPE_PAYMENT_INTENT_REQUIRES_ACTION = 'payment_intent.requires_action';

    public const TYPE_PAYMENT_INTENT_CANCELED = 'payment_intent.canceled';

    public const TYPE_SETUP_INTENT_SUCCEEDED = 'setup_intent.succeeded';

    public const TYPE_SETUP_INTENT_SETUP_FAILED = 'setup_intent.setup_failed';

    public const TYPE_MANDATE_UPDATED = 'mandate.updated';

    public const TYPE_CHARGE_REFUNDED = 'charge.refunded';

    public const TYPE_CHARGE_DISPUTE_CREATED = 'charge.dispute.created';

    public const TYPE_CHARGE_DISPUTE_UPDATED = 'charge.dispute.updated';

    public const TYPE_CHARGE_DISPUTE_CLOSED = 'charge.dispute.closed';

    public const TYPE_CHARGE_DISPUTE_FUNDS_WITHDRAWN = 'charge.dispute.funds_withdrawn';

    public const TYPE_CHARGE_DISPUTE_FUNDS_REINSTATED = 'charge.dispute.funds_reinstated';

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
