<?php

namespace Pay\Credit;

class Credit
{
    public const STATUS_ACTIVE = 'active';

    public const STATUS_APPLIED = 'applied';

    public const STATUS_EXPIRED = 'expired';

    /**
     * @param  string  $id
     * @param  float  $credits
     * @param  int  $creditsUsed
     * @param  string  $status
     */
    public function __construct(private string $id, private float $credits, private float $creditsUsed = 0, private string $status = self::STATUS_ACTIVE)
    {
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function markAsApplied(): static
    {
        $this->status = self::STATUS_APPLIED;

        return $this;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getCredits(): float
    {
        return $this->credits;
    }

    public function setCredits(float $credits): static
    {
        $this->credits = $credits;

        return $this;
    }

    public function getCreditsUsed(): float
    {
        return $this->creditsUsed;
    }

    public function setCreditsUsed(float $creditsUsed): static
    {
        $this->creditsUsed = $creditsUsed;

        return $this;
    }

    public function hasAvailableCredits(): bool
    {
        return $this->credits > 0;
    }

    public function useCredits(float $amount): float
    {
        if ($amount <= 0) {
            return 0;
        }

        $creditsToUse = min($amount, $this->credits);
        $this->credits -= $creditsToUse;
        $this->creditsUsed += $creditsToUse;

        if ($this->credits === 0) {
            $this->status = self::STATUS_APPLIED;
        }

        return $creditsToUse;
    }

    public function setStatus($status): static
    {
        $this->status = $status;

        return $this;
    }

    public function isFullyUsed(): bool
    {
        return $this->credits === 0 || $this->status === self::STATUS_APPLIED;
    }

    public static function fromArray(array $data): self
    {
        return new self($data['id'] ?? $data['$id'] ?? '',
            $data['credits'] ?? 0.0,
            $data['creditsUsed'] ?? 0.0,
            $data['status'] ?? self::STATUS_ACTIVE
        );
    }

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
