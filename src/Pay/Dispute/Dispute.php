<?php

namespace Utopia\Pay\Dispute;

use Utopia\Pay\Model;

/**
 * Dispute returned by listDisputes() or carried by a charge.dispute.* webhook event
 */
class Dispute extends Model
{
    public function getId(): ?string
    {
        return $this->string('id');
    }

    /**
     * Amount in the smallest currency unit
     */
    public function getAmount(): ?int
    {
        return $this->int('amount');
    }

    public function getCurrency(): ?string
    {
        return $this->string('currency');
    }

    public function getReason(): ?string
    {
        return $this->string('reason');
    }

    public function getStatus(): ?string
    {
        return $this->string('status');
    }

    public function getChargeId(): ?string
    {
        return $this->expandableId('charge');
    }

    public function getPaymentIntentId(): ?string
    {
        return $this->expandableId('payment_intent');
    }

    /**
     * Metadata of the payment intent, only available when `payment_intent` is expanded
     *
     * @return array<string, mixed>|null
     */
    public function getPaymentIntentMetadata(): ?array
    {
        if ($this->array('payment_intent') === null) {
            return null;
        }

        return $this->array('payment_intent', 'metadata') ?? [];
    }

    /**
     * @return array<string, mixed>
     */
    public function getMetadata(): array
    {
        return $this->array('metadata') ?? [];
    }

    /**
     * Unix timestamp by which evidence must be submitted
     */
    public function getEvidenceDueBy(): ?int
    {
        return $this->int('evidence_details', 'due_by');
    }

    public function getCreatedAt(): ?int
    {
        return $this->int('created');
    }
}
