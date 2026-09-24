<?php

namespace Utopia\Pay\SetupIntent;

use Utopia\Pay\Model;

/**
 * Setup intent returned by createFuturePayment(), getFuturePayment(), updateFuturePayment() and listFuturePayments()
 */
class SetupIntent extends Model
{
    public const STATUS_SUCCEEDED = 'succeeded';

    public function getId(): ?string
    {
        return $this->string('id');
    }

    public function getStatus(): ?string
    {
        return $this->string('status');
    }

    public function getCustomerId(): ?string
    {
        return $this->expandableId('customer');
    }

    public function getPaymentMethodId(): ?string
    {
        return $this->expandableId('payment_method');
    }

    public function getClientSecret(): ?string
    {
        return $this->string('client_secret');
    }

    public function getMandateId(): ?string
    {
        return $this->expandableId('mandate');
    }

    /**
     * Free-form per-type options, e.g. `card.mandate_options`
     *
     * @return array<string, mixed>|null
     */
    public function getPaymentMethodOptions(): ?array
    {
        return $this->array('payment_method_options');
    }

    public function isSucceeded(): bool
    {
        return $this->getStatus() === self::STATUS_SUCCEEDED;
    }
}
