<?php

namespace Utopia\Pay\Payment;

use Utopia\Pay\Charge\Charge;
use Utopia\Pay\Model;

/**
 * Payment intent returned by purchase(), authorize(), capture(), getPayment() and the other payment calls
 */
class Payment extends Model
{
    public const STATUS_REQUIRES_PAYMENT_METHOD = 'requires_payment_method';

    public const STATUS_REQUIRES_CONFIRMATION = 'requires_confirmation';

    public const STATUS_REQUIRES_ACTION = 'requires_action';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_REQUIRES_CAPTURE = 'requires_capture';

    public const STATUS_CANCELED = 'canceled';

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

    public function getAmountReceived(): ?int
    {
        return $this->int('amount_received');
    }

    public function getCurrency(): ?string
    {
        return $this->string('currency');
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

    public function getLatestChargeId(): ?string
    {
        return $this->expandableId('latest_charge');
    }

    /**
     * Charges from the legacy `charges` list, empty on API versions that only send `latest_charge`
     *
     * @return array<Charge>
     */
    public function getCharges(): array
    {
        return $this->list(Charge::class, 'charges');
    }

    /**
     * `last_payment_error.code` of the last failed attempt
     */
    public function getErrorCode(): ?string
    {
        return $this->string('last_payment_error', 'code');
    }

    public function getDeclineCode(): ?string
    {
        return $this->string('last_payment_error', 'decline_code');
    }

    public function getErrorMessage(): ?string
    {
        return $this->string('last_payment_error', 'message');
    }

    /**
     * Free-form instructions for completing authentication, e.g. `use_stripe_sdk`
     *
     * @return array<string, mixed>|null
     */
    public function getNextAction(): ?array
    {
        return $this->array('next_action');
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

    public function isProcessing(): bool
    {
        return $this->getStatus() === self::STATUS_PROCESSING;
    }

    public function isCanceled(): bool
    {
        return $this->getStatus() === self::STATUS_CANCELED;
    }

    public function requiresAction(): bool
    {
        return $this->getStatus() === self::STATUS_REQUIRES_ACTION;
    }

    public function requiresCapture(): bool
    {
        return $this->getStatus() === self::STATUS_REQUIRES_CAPTURE;
    }

    public function requiresPaymentMethod(): bool
    {
        return $this->getStatus() === self::STATUS_REQUIRES_PAYMENT_METHOD;
    }
}
