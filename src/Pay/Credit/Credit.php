<?php

namespace Utopia\Pay\Credit;

class Credit
{
    public const STATUS_ACTIVE = 'active';

    public const STATUS_APPLIED = 'applied';

    public const STATUS_EXPIRED = 'expired';

    /**
     * Credit constructor.
     *
     * @param  string  $id
     * @param  float  $credits
     * @param  float  $creditsUsed
     * @param  string  $status
     */
    public function __construct(private string $id, private float $credits, private float $creditsUsed = 0, private string $status = self::STATUS_ACTIVE)
    {
    }

    /**
     * Get the credit status.
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * Mark the credit as applied.
     */
    public function markAsApplied(): static
    {
        $this->status = self::STATUS_APPLIED;

        return $this;
    }

    /**
     * Get the credit ID.
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Set the credit ID.
     */
    public function setId(string $id): static
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the available credits.
     */
    public function getCredits(): float
    {
        return $this->credits;
    }

    /**
     * Set the available credits.
     */
    public function setCredits(float $credits): static
    {
        $this->credits = $credits;

        return $this;
    }

    /**
     * Get the credits used.
     */
    public function getCreditsUsed(): float
    {
        return $this->creditsUsed;
    }

    /**
     * Set the credits used.
     */
    public function setCreditsUsed(float $creditsUsed): static
    {
        $this->creditsUsed = $creditsUsed;

        return $this;
    }

    /**
     * Check if there are available credits.
     */
    public function hasAvailableCredits(): bool
    {
        return $this->credits > 0;
    }

    /**
     * Use credits for a given amount.
     *
     * @param  float  $amount
     * @return float Credits actually used
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
     * @param  string  $status
     * @return static
     */
    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Check if the credit is fully used.
     */
    public function isFullyUsed(): bool
    {
        return $this->credits === 0 || $this->status === self::STATUS_APPLIED;
    }

    /**
     * Create a Credit object from an array.
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
     * Convert the credit to an array.
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
