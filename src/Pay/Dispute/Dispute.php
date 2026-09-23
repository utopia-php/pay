<?php

namespace Utopia\Pay\Dispute;

use Utopia\Pay\Expandable;

/**
 * Typed view of a dispute, from listDisputes() or a charge.dispute.* webhook event.
 */
class Dispute
{
    use Expandable;

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        private string $id,
        private int $amount,
        private string $currency,
        private string $reason,
        private string $status,
        private ?string $chargeId = null,
        private ?string $paymentIntentId = null,
        private array $metadata = [],
        private ?int $evidenceDueBy = null,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Amount in the smallest currency unit
     */
    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getReason(): string
    {
        return $this->reason;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getChargeId(): ?string
    {
        return $this->chargeId;
    }

    public function getPaymentIntentId(): ?string
    {
        return $this->paymentIntentId;
    }

    /**
     * @return array<string, mixed>
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    /**
     * Unix timestamp by which evidence must be submitted
     */
    public function getEvidenceDueBy(): ?int
    {
        return $this->evidenceDueBy;
    }

    /**
     * @param  array<string, mixed>  $data  Dispute payload
     */
    public static function fromArray(array $data): self
    {
        $dueBy = $data['evidence_details']['due_by'] ?? null;

        return new self(
            id: (string) ($data['id'] ?? ''),
            amount: (int) ($data['amount'] ?? 0),
            currency: (string) ($data['currency'] ?? ''),
            reason: (string) ($data['reason'] ?? ''),
            status: (string) ($data['status'] ?? ''),
            chargeId: self::expandableId($data['charge'] ?? null),
            paymentIntentId: self::expandableId($data['payment_intent'] ?? null),
            metadata: $data['metadata'] ?? [],
            evidenceDueBy: is_int($dueBy) && $dueBy > 0 ? $dueBy : null,
        );
    }
}
