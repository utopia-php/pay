<?php

namespace Utopia\Pay\Refund;

use Utopia\Pay\Model;

/**
 * Refund returned by refund()
 */
class Refund extends Model
{
    public const STATUS_SUCCEEDED = 'succeeded';

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

    public function getStatus(): ?string
    {
        return $this->string('status');
    }

    public function getPaymentIntentId(): ?string
    {
        return $this->expandableId('payment_intent');
    }

    public function getChargeId(): ?string
    {
        return $this->expandableId('charge');
    }

    public function getReason(): ?string
    {
        return $this->string('reason');
    }

    public function getFailureReason(): ?string
    {
        return $this->string('failure_reason');
    }

    /**
     * @return array<string, mixed>
     */
    public function getMetadata(): array
    {
        return $this->array('metadata') ?? [];
    }

    public function getCreatedAt(): ?int
    {
        return $this->int('created');
    }

    public function isSucceeded(): bool
    {
        return $this->getStatus() === self::STATUS_SUCCEEDED;
    }
}
