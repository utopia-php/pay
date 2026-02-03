<?php

namespace Utopia\Pay\Dispute;

/**
 * Dispute class for managing payment dispute/chargeback data.
 *
 * Represents a dispute (chargeback) on a payment.
 */
class Dispute
{
    /**
     * Dispute requires response.
     */
    public const STATUS_WARNING_NEEDS_RESPONSE = 'warning_needs_response';

    /**
     * Warning under review.
     */
    public const STATUS_WARNING_UNDER_REVIEW = 'warning_under_review';

    /**
     * Warning closed.
     */
    public const STATUS_WARNING_CLOSED = 'warning_closed';

    /**
     * Dispute needs response.
     */
    public const STATUS_NEEDS_RESPONSE = 'needs_response';

    /**
     * Dispute under review.
     */
    public const STATUS_UNDER_REVIEW = 'under_review';

    /**
     * Dispute won.
     */
    public const STATUS_WON = 'won';

    /**
     * Dispute lost.
     */
    public const STATUS_LOST = 'lost';

    /**
     * Dispute reason: duplicate charge.
     */
    public const REASON_DUPLICATE = 'duplicate';

    /**
     * Dispute reason: fraudulent.
     */
    public const REASON_FRAUDULENT = 'fraudulent';

    /**
     * Dispute reason: subscription canceled.
     */
    public const REASON_SUBSCRIPTION_CANCELED = 'subscription_canceled';

    /**
     * Dispute reason: product unacceptable.
     */
    public const REASON_PRODUCT_UNACCEPTABLE = 'product_unacceptable';

    /**
     * Dispute reason: product not received.
     */
    public const REASON_PRODUCT_NOT_RECEIVED = 'product_not_received';

    /**
     * Dispute reason: unrecognized.
     */
    public const REASON_UNRECOGNIZED = 'unrecognized';

    /**
     * Dispute reason: credit not processed.
     */
    public const REASON_CREDIT_NOT_PROCESSED = 'credit_not_processed';

    /**
     * Dispute reason: general.
     */
    public const REASON_GENERAL = 'general';

    /**
     * Dispute reason: incorrect account details.
     */
    public const REASON_INCORRECT_ACCOUNT_DETAILS = 'incorrect_account_details';

    /**
     * Dispute reason: insufficient funds.
     */
    public const REASON_INSUFFICIENT_FUNDS = 'insufficient_funds';

    /**
     * Dispute reason: bank cannot process.
     */
    public const REASON_BANK_CANNOT_PROCESS = 'bank_cannot_process';

    /**
     * Dispute reason: debit not authorized.
     */
    public const REASON_DEBIT_NOT_AUTHORIZED = 'debit_not_authorized';

    /**
     * Create a new Dispute instance.
     *
     * @param  string  $id  Unique identifier for the dispute
     * @param  int  $amount  Disputed amount in smallest currency unit
     * @param  string  $currency  Three-letter ISO currency code
     * @param  string  $status  Dispute status
     * @param  string|null  $chargeId  The charge ID this dispute is for
     * @param  string|null  $paymentIntentId  The payment intent ID this dispute is for
     * @param  string|null  $reason  Reason for the dispute
     * @param  bool  $isChargeRefundable  Whether the charge is refundable
     * @param  int|null  $evidenceDueBy  Unix timestamp for evidence submission deadline
     * @param  bool  $hasEvidence  Whether evidence has been submitted
     * @param  bool  $pastDue  Whether the evidence submission is past due
     * @param  string|null  $networkReasonCode  Network-specific reason code
     * @param  array<string, mixed>  $metadata  Additional metadata
     * @param  int|null  $createdAt  Unix timestamp when dispute was created
     */
    public function __construct(
        private string $id,
        private int $amount,
        private string $currency,
        private string $status = self::STATUS_NEEDS_RESPONSE,
        private ?string $chargeId = null,
        private ?string $paymentIntentId = null,
        private ?string $reason = null,
        private bool $isChargeRefundable = false,
        private ?int $evidenceDueBy = null,
        private bool $hasEvidence = false,
        private bool $pastDue = false,
        private ?string $networkReasonCode = null,
        private array $metadata = [],
        private ?int $createdAt = null
    ) {
        $this->createdAt = $createdAt ?? time();
    }

    /**
     * Get the dispute ID.
     *
     * @return string The unique dispute identifier
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Set the dispute ID.
     *
     * @param  string  $id  The dispute ID
     * @return static
     */
    public function setId(string $id): static
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the disputed amount.
     *
     * @return int The amount in smallest currency unit
     */
    public function getAmount(): int
    {
        return $this->amount;
    }

    /**
     * Set the disputed amount.
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
     * Get the dispute status.
     *
     * @return string The dispute status
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * Set the dispute status.
     *
     * @param  string  $status  The dispute status
     * @return static
     */
    public function setStatus(string $status): static
    {
        $this->status = $status;

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
     * Get the payment intent ID.
     *
     * @return string|null The payment intent ID
     */
    public function getPaymentIntentId(): ?string
    {
        return $this->paymentIntentId;
    }

    /**
     * Set the payment intent ID.
     *
     * @param  string|null  $paymentIntentId  The payment intent ID
     * @return static
     */
    public function setPaymentIntentId(?string $paymentIntentId): static
    {
        $this->paymentIntentId = $paymentIntentId;

        return $this;
    }

    /**
     * Get the dispute reason.
     *
     * @return string|null The reason for the dispute
     */
    public function getReason(): ?string
    {
        return $this->reason;
    }

    /**
     * Set the dispute reason.
     *
     * @param  string|null  $reason  The reason for the dispute
     * @return static
     */
    public function setReason(?string $reason): static
    {
        $this->reason = $reason;

        return $this;
    }

    /**
     * Check if the charge is refundable.
     *
     * @return bool True if charge can be refunded
     */
    public function isChargeRefundable(): bool
    {
        return $this->isChargeRefundable;
    }

    /**
     * Set whether the charge is refundable.
     *
     * @param  bool  $isChargeRefundable  Whether charge is refundable
     * @return static
     */
    public function setIsChargeRefundable(bool $isChargeRefundable): static
    {
        $this->isChargeRefundable = $isChargeRefundable;

        return $this;
    }

    /**
     * Get the evidence due by timestamp.
     *
     * @return int|null Unix timestamp for evidence deadline
     */
    public function getEvidenceDueBy(): ?int
    {
        return $this->evidenceDueBy;
    }

    /**
     * Set the evidence due by timestamp.
     *
     * @param  int|null  $evidenceDueBy  Unix timestamp
     * @return static
     */
    public function setEvidenceDueBy(?int $evidenceDueBy): static
    {
        $this->evidenceDueBy = $evidenceDueBy;

        return $this;
    }

    /**
     * Check if evidence has been submitted.
     *
     * @return bool True if evidence has been submitted
     */
    public function hasEvidence(): bool
    {
        return $this->hasEvidence;
    }

    /**
     * Set whether evidence has been submitted.
     *
     * @param  bool  $hasEvidence  Whether evidence is submitted
     * @return static
     */
    public function setHasEvidence(bool $hasEvidence): static
    {
        $this->hasEvidence = $hasEvidence;

        return $this;
    }

    /**
     * Check if evidence submission is past due.
     *
     * @return bool True if past due
     */
    public function isPastDue(): bool
    {
        return $this->pastDue;
    }

    /**
     * Set whether evidence submission is past due.
     *
     * @param  bool  $pastDue  Whether past due
     * @return static
     */
    public function setPastDue(bool $pastDue): static
    {
        $this->pastDue = $pastDue;

        return $this;
    }

    /**
     * Get the network reason code.
     *
     * @return string|null The network-specific reason code
     */
    public function getNetworkReasonCode(): ?string
    {
        return $this->networkReasonCode;
    }

    /**
     * Set the network reason code.
     *
     * @param  string|null  $networkReasonCode  The network reason code
     * @return static
     */
    public function setNetworkReasonCode(?string $networkReasonCode): static
    {
        $this->networkReasonCode = $networkReasonCode;

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
     * Check if dispute is won.
     *
     * @return bool True if dispute was won
     */
    public function isWon(): bool
    {
        return $this->status === self::STATUS_WON;
    }

    /**
     * Check if dispute is lost.
     *
     * @return bool True if dispute was lost
     */
    public function isLost(): bool
    {
        return $this->status === self::STATUS_LOST;
    }

    /**
     * Check if dispute needs response.
     *
     * @return bool True if response is needed
     */
    public function needsResponse(): bool
    {
        return in_array($this->status, [
            self::STATUS_NEEDS_RESPONSE,
            self::STATUS_WARNING_NEEDS_RESPONSE,
        ]);
    }

    /**
     * Check if dispute is under review.
     *
     * @return bool True if under review
     */
    public function isUnderReview(): bool
    {
        return in_array($this->status, [
            self::STATUS_UNDER_REVIEW,
            self::STATUS_WARNING_UNDER_REVIEW,
        ]);
    }

    /**
     * Check if dispute is closed.
     *
     * @return bool True if dispute is closed
     */
    public function isClosed(): bool
    {
        return in_array($this->status, [
            self::STATUS_WON,
            self::STATUS_LOST,
            self::STATUS_WARNING_CLOSED,
        ]);
    }

    /**
     * Check if this is a warning (inquiry).
     *
     * @return bool True if this is a warning
     */
    public function isWarning(): bool
    {
        return str_starts_with($this->status, 'warning_');
    }

    /**
     * Get the amount as a formatted decimal.
     *
     * @param  int  $decimals  Number of decimal places (default: 2)
     * @return float The amount as a decimal
     */
    public function getAmountDecimal(int $decimals = 2): float
    {
        return round($this->amount / 100, $decimals);
    }

    /**
     * Get days remaining to submit evidence.
     *
     * @return int|null Days remaining, or null if no deadline
     */
    public function getDaysRemaining(): ?int
    {
        if ($this->evidenceDueBy === null) {
            return null;
        }

        $now = time();
        $diff = $this->evidenceDueBy - $now;

        return max(0, (int) ceil($diff / 86400));
    }

    /**
     * Convert the dispute to an array representation.
     *
     * @return array<string, mixed> The dispute data as an array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'status' => $this->status,
            'chargeId' => $this->chargeId,
            'paymentIntentId' => $this->paymentIntentId,
            'reason' => $this->reason,
            'isChargeRefundable' => $this->isChargeRefundable,
            'evidenceDueBy' => $this->evidenceDueBy,
            'hasEvidence' => $this->hasEvidence,
            'pastDue' => $this->pastDue,
            'networkReasonCode' => $this->networkReasonCode,
            'metadata' => $this->metadata,
            'createdAt' => $this->createdAt,
        ];
    }

    /**
     * Create a Dispute instance from an array.
     *
     * @param  array<string, mixed>  $data  The dispute data array
     * @return self The created Dispute instance
     */
    public static function fromArray(array $data): self
    {
        // Handle Stripe's evidence_details structure
        $evidenceDetails = $data['evidence_details'] ?? [];

        return new self(
            id: $data['id'] ?? $data['$id'] ?? uniqid('dp_'),
            amount: (int) ($data['amount'] ?? 0),
            currency: strtoupper($data['currency'] ?? 'USD'),
            status: $data['status'] ?? self::STATUS_NEEDS_RESPONSE,
            chargeId: $data['chargeId'] ?? $data['charge'] ?? null,
            paymentIntentId: $data['paymentIntentId'] ?? $data['payment_intent'] ?? null,
            reason: $data['reason'] ?? null,
            isChargeRefundable: $data['isChargeRefundable'] ?? $data['is_charge_refundable'] ?? false,
            evidenceDueBy: $data['evidenceDueBy'] ?? $evidenceDetails['due_by'] ?? null,
            hasEvidence: $data['hasEvidence'] ?? $evidenceDetails['has_evidence'] ?? false,
            pastDue: $data['pastDue'] ?? $evidenceDetails['past_due'] ?? false,
            networkReasonCode: $data['networkReasonCode'] ?? $data['network_reason_code'] ?? null,
            metadata: $data['metadata'] ?? [],
            createdAt: $data['createdAt'] ?? $data['created'] ?? null
        );
    }
}
