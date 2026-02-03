<?php

namespace Utopia\Pay\Refund;

/**
 * Refund class for managing refund data.
 *
 * Represents a refund for a payment in the payment system.
 */
class Refund
{
    /**
     * Refund is pending.
     */
    public const STATUS_PENDING = 'pending';

    /**
     * Refund succeeded.
     */
    public const STATUS_SUCCEEDED = 'succeeded';

    /**
     * Refund failed.
     */
    public const STATUS_FAILED = 'failed';

    /**
     * Refund was cancelled.
     */
    public const STATUS_CANCELLED = 'canceled';

    /**
     * Refund requires action.
     */
    public const STATUS_REQUIRES_ACTION = 'requires_action';

    /**
     * Refund reason: duplicate charge.
     */
    public const REASON_DUPLICATE = 'duplicate';

    /**
     * Refund reason: fraudulent charge.
     */
    public const REASON_FRAUDULENT = 'fraudulent';

    /**
     * Refund reason: customer requested.
     */
    public const REASON_REQUESTED_BY_CUSTOMER = 'requested_by_customer';

    /**
     * Create a new Refund instance.
     *
     * @param  string  $id  Unique identifier for the refund
     * @param  int  $amount  Refund amount in smallest currency unit
     * @param  string  $currency  Three-letter ISO currency code
     * @param  string  $status  Refund status
     * @param  string|null  $paymentId  The payment intent ID this refund is for
     * @param  string|null  $chargeId  The charge ID this refund is for
     * @param  string|null  $reason  Reason for the refund
     * @param  string|null  $failureReason  Reason for refund failure
     * @param  string|null  $receiptNumber  Receipt number
     * @param  array<string, mixed>  $metadata  Additional metadata
     * @param  int|null  $createdAt  Unix timestamp when refund was created
     */
    public function __construct(
        private string $id,
        private int $amount,
        private string $currency,
        private string $status = self::STATUS_PENDING,
        private ?string $paymentId = null,
        private ?string $chargeId = null,
        private ?string $reason = null,
        private ?string $failureReason = null,
        private ?string $receiptNumber = null,
        private array $metadata = [],
        private ?int $createdAt = null
    ) {
        $this->createdAt = $createdAt ?? time();
    }

    /**
     * Get the refund ID.
     *
     * @return string The unique refund identifier
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Set the refund ID.
     *
     * @param  string  $id  The refund ID
     * @return static
     */
    public function setId(string $id): static
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the refund amount.
     *
     * @return int The amount in smallest currency unit
     */
    public function getAmount(): int
    {
        return $this->amount;
    }

    /**
     * Set the refund amount.
     *
     * @param  int  $amount  The amount in smallest currency unit
     * @return static
     */
    public function setAmount(int $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get the currency code.
     *
     * @return string Three-letter ISO currency code
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * Set the currency code.
     *
     * @param  string  $currency  Three-letter ISO currency code
     * @return static
     */
    public function setCurrency(string $currency): static
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * Get the refund status.
     *
     * @return string The refund status
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * Set the refund status.
     *
     * @param  string  $status  The refund status
     * @return static
     */
    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get the payment ID.
     *
     * @return string|null The payment intent ID
     */
    public function getPaymentId(): ?string
    {
        return $this->paymentId;
    }

    /**
     * Set the payment ID.
     *
     * @param  string|null  $paymentId  The payment intent ID
     * @return static
     */
    public function setPaymentId(?string $paymentId): static
    {
        $this->paymentId = $paymentId;

        return $this;
    }

    /**
     * Get the charge ID.
     *
     * @return string|null The charge ID
     */
    public function getChargeId(): ?string
    {
        return $this->chargeId;
    }

    /**
     * Set the charge ID.
     *
     * @param  string|null  $chargeId  The charge ID
     * @return static
     */
    public function setChargeId(?string $chargeId): static
    {
        $this->chargeId = $chargeId;

        return $this;
    }

    /**
     * Get the refund reason.
     *
     * @return string|null The reason for the refund
     */
    public function getReason(): ?string
    {
        return $this->reason;
    }

    /**
     * Set the refund reason.
     *
     * @param  string|null  $reason  The reason for the refund
     * @return static
     */
    public function setReason(?string $reason): static
    {
        $this->reason = $reason;

        return $this;
    }

    /**
     * Get the failure reason.
     *
     * @return string|null The reason for refund failure
     */
    public function getFailureReason(): ?string
    {
        return $this->failureReason;
    }

    /**
     * Set the failure reason.
     *
     * @param  string|null  $failureReason  The reason for refund failure
     * @return static
     */
    public function setFailureReason(?string $failureReason): static
    {
        $this->failureReason = $failureReason;

        return $this;
    }

    /**
     * Get the receipt number.
     *
     * @return string|null The receipt number
     */
    public function getReceiptNumber(): ?string
    {
        return $this->receiptNumber;
    }

    /**
     * Set the receipt number.
     *
     * @param  string|null  $receiptNumber  The receipt number
     * @return static
     */
    public function setReceiptNumber(?string $receiptNumber): static
    {
        $this->receiptNumber = $receiptNumber;

        return $this;
    }

    /**
     * Get the metadata.
     *
     * @return array<string, mixed> The metadata
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    /**
     * Set the metadata.
     *
     * @param  array<string, mixed>  $metadata  The metadata
     * @return static
     */
    public function setMetadata(array $metadata): static
    {
        $this->metadata = $metadata;

        return $this;
    }

    /**
     * Get the creation timestamp.
     *
     * @return int|null Unix timestamp
     */
    public function getCreatedAt(): ?int
    {
        return $this->createdAt;
    }

    /**
     * Set the creation timestamp.
     *
     * @param  int|null  $createdAt  Unix timestamp
     * @return static
     */
    public function setCreatedAt(?int $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Check if refund succeeded.
     *
     * @return bool True if refund succeeded
     */
    public function isSucceeded(): bool
    {
        return $this->status === self::STATUS_SUCCEEDED;
    }

    /**
     * Check if refund is pending.
     *
     * @return bool True if refund is pending
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if refund failed.
     *
     * @return bool True if refund failed
     */
    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    /**
     * Check if refund was cancelled.
     *
     * @return bool True if refund was cancelled
     */
    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /**
     * Get the amount as a formatted decimal (for display).
     *
     * @param  int  $decimals  Number of decimal places (default: 2)
     * @return float The amount as a decimal
     */
    public function getAmountDecimal(int $decimals = 2): float
    {
        return round($this->amount / 100, $decimals);
    }

    /**
     * Convert the refund to an array representation.
     *
     * @return array<string, mixed> The refund data as an array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'status' => $this->status,
            'paymentId' => $this->paymentId,
            'chargeId' => $this->chargeId,
            'reason' => $this->reason,
            'failureReason' => $this->failureReason,
            'receiptNumber' => $this->receiptNumber,
            'metadata' => $this->metadata,
            'createdAt' => $this->createdAt,
        ];
    }

    /**
     * Create a Refund instance from an array.
     *
     * @param  array<string, mixed>  $data  The refund data array
     * @return self The created Refund instance
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? $data['$id'] ?? uniqid('re_'),
            amount: (int) ($data['amount'] ?? 0),
            currency: strtoupper($data['currency'] ?? 'USD'),
            status: $data['status'] ?? self::STATUS_PENDING,
            paymentId: $data['paymentId'] ?? $data['payment_intent'] ?? null,
            chargeId: $data['chargeId'] ?? $data['charge'] ?? null,
            reason: $data['reason'] ?? null,
            failureReason: $data['failureReason'] ?? $data['failure_reason'] ?? null,
            receiptNumber: $data['receiptNumber'] ?? $data['receipt_number'] ?? null,
            metadata: $data['metadata'] ?? [],
            createdAt: $data['createdAt'] ?? $data['created'] ?? null
        );
    }
}
