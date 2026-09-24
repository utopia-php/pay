<?php

namespace Utopia\Pay\Charge;

use Utopia\Pay\Model;

/**
 * Charge attached to a payment intent
 */
class Charge extends Model
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

    public function getAmountRefunded(): ?int
    {
        return $this->int('amount_refunded');
    }

    public function getCurrency(): ?string
    {
        return $this->string('currency');
    }

    public function getStatus(): ?string
    {
        return $this->string('status');
    }

    public function isRefunded(): bool
    {
        return $this->bool('refunded') === true;
    }
}
