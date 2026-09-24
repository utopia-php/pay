<?php

namespace Utopia\Pay\Mandate;

use Utopia\Pay\Expandable;

/**
 * Typed view of a mandate, from getMandate() or a mandate.updated webhook event.
 */
class Mandate
{
    use Expandable;

    public const STATUS_ACTIVE = 'active';

    public function __construct(
        private string $id,
        private string $status,
        private ?string $paymentMethodId = null,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getPaymentMethodId(): ?string
    {
        return $this->paymentMethodId;
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * @param  array<string, mixed>  $data  Mandate payload
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: (string) ($data['id'] ?? ''),
            status: (string) ($data['status'] ?? ''),
            paymentMethodId: self::expandableId($data['payment_method'] ?? null),
        );
    }
}
