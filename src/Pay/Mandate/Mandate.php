<?php

namespace Utopia\Pay\Mandate;

use Utopia\Pay\Model;

/**
 * Mandate returned by getMandate() or carried by a mandate.updated webhook event
 */
class Mandate extends Model
{
    public const STATUS_ACTIVE = 'active';

    public function getId(): ?string
    {
        return $this->string('id');
    }

    public function getStatus(): ?string
    {
        return $this->string('status');
    }

    public function getPaymentMethodId(): ?string
    {
        return $this->expandableId('payment_method');
    }

    public function isActive(): bool
    {
        return $this->getStatus() === self::STATUS_ACTIVE;
    }
}
