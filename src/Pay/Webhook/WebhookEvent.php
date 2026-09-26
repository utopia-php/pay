<?php

namespace Utopia\Pay\Webhook;

use Utopia\Pay\Model;

/**
 * Verified webhook event returned by constructWebhookEvent()
 */
class WebhookEvent extends Model
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

    public function getId(): ?string
    {
        return $this->string('id');
    }

    public function getType(): ?string
    {
        return $this->string('type');
    }

    /**
     * The resource the event is about (`data.object`), e.g. a payment intent or dispute payload
     *
     * @return array<string, mixed>
     */
    public function getObject(): array
    {
        return $this->array('data', 'object') ?? [];
    }

    /**
     * Kind of resource in getObject(), e.g. `payment_intent`, `mandate` or `dispute`
     */
    public function getObjectType(): ?string
    {
        return $this->string('data', 'object', 'object');
    }
}
