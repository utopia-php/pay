<?php

namespace Utopia\Pay\Credit;

/**
 * Credit class for managing user credits and credit balances.
 *
 * Credits can be applied to invoices to reduce the amount due.
 * Tracks both available credits and credits used, with status management.
 */
class Credit
{
    /**
     * Credit is active and available for use.
     */
    public const STATUS_ACTIVE = 'active';

    /**
     * Credit has been fully applied to an invoice.
     */
    public const STATUS_APPLIED = 'applied';

    /**
     * Credit has expired and can no longer be used.
     */
    public const STATUS_EXPIRED = 'expired';

    /**
     * Create a new Credit instance.
     *
     * @param  string  $id  Unique identifier for the credit
     * @param  float  $credits  The amount of credits available
     * @param  float  $creditsUsed  The amount of credits already used (default: 0)
     * @param  string  $status  The credit status (default: STATUS_ACTIVE)
     */
    public function __construct(private string $id, private float $credits, private float $creditsUsed = 0, private string $status = self::STATUS_ACTIVE)
    {
    }

    /**
     * Get the credit status.
     *
     * @return string The current status (one of STATUS_* constants)
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * Mark the credit as applied (fully used).
     *
     * @return static
     */
    public function markAsApplied(): static
    {
        $this->status = self::STATUS_APPLIED;

        return $this;
    }

    /**
     * Get the credit ID.
     *
     * @return string The unique credit identifier
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Set the credit ID.
     *
     * @param  string  $id  The credit ID
     * @return static
     */
    public function setId(string $id): static
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the available credits.
     *
     * @return float The amount of credits available
     */
    public function getCredits(): float
    {
        return $this->credits;
    }

    /**
     * Set the available credits.
     *
     * @param  float  $credits  The amount of credits to set
     * @return static
     */
    public function setCredits(float $credits): static
    {
        $this->credits = $credits;

        return $this;
    }

    /**
     * Get the credits used.
     *
     * @return float The amount of credits already used
     */
    public function getCreditsUsed(): float
    {
        return $this->creditsUsed;
    }

    /**
     * Set the credits used.
     *
     * @param  float  $creditsUsed  The amount of credits used
     * @return static
     */
    public function setCreditsUsed(float $creditsUsed): static
    {
        $this->creditsUsed = $creditsUsed;

        return $this;
    }

    /**
     * Check if there are available credits.
     *
     * @return bool True if credits are available (greater than 0)
     */
    public function hasAvailableCredits(): bool
    {
        return $this->credits > 0;
    }

    /**
     * Use credits for a given amount.
     *
     * Reduces available credits by the amount used (up to the available balance).
     * Automatically marks the credit as applied when fully used.
     *
     * @param  float  $amount  The amount to apply credits to
     * @return float The amount of credits actually used
     */
    public function useCredits(float $amount): float
    {
        if ($amount <= 0) {
            return 0;
        }

        if ($this->credits <= 0) {
            $this->status = self::STATUS_APPLIED;

            return $amount;
        }
        $creditsToUse = min($amount, $this->credits);
        $this->credits -= $creditsToUse;
        $this->creditsUsed += $creditsToUse;
        if ($this->credits === 0) {
            $this->status = self::STATUS_APPLIED;
        }

        return $creditsToUse;
    }

    /**
     * Set the credit status.
     *
     * @param  string  $status  The status to set (use STATUS_* constants)
     * @return static
     */
    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Check if the credit is fully used.
     *
     * @return bool True if no credits remain or status is applied
     */
    public function isFullyUsed(): bool
    {
        return $this->credits === 0 || $this->status === self::STATUS_APPLIED;
    }

    /**
     * Create a Credit instance from an array.
     *
     * @param  array  $data  The credit data array
     * @return self The created Credit instance
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['id'] ?? $data['$id'] ?? uniqid('credit_'),
            $data['credits'] ?? 0.0,
            $data['creditsUsed'] ?? 0.0,
            $data['status'] ?? self::STATUS_ACTIVE
        );
    }

    /**
     * Convert the credit to an array representation.
     *
     * @return array The credit data as an array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'credits' => $this->credits,
            'creditsUsed' => $this->creditsUsed,
            'status' => $this->status,
        ];
    }
}
