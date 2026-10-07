<?php

namespace Utopia\Pay\Credit;

final readonly class Credit
{
    public const STATUS_ACTIVE = 'active';

    public const STATUS_APPLIED = 'applied';

    public const STATUS_EXPIRED = 'expired';

    public function __construct(
        public string $id,
        public float $credits,
        public float $creditsUsed = 0,
        public string $status = self::STATUS_ACTIVE,
    ) {
    }

    public function hasAvailableCredits(): bool
    {
        return $this->credits > 0;
    }

    /** Return the remaining balance after using at most the available credits. */
    public function useCredits(float $amount): self
    {
        if ($amount <= 0) {
            return $this;
        }

        $used = min($amount, max(0.0, $this->credits));
        $remaining = $this->credits - $used;

        return new self($this->id, $remaining, $this->creditsUsed + $used, $remaining <= 0 ? self::STATUS_APPLIED : $this->status);
    }

    public function isFullyUsed(): bool
    {
        return $this->credits <= 0 || $this->status === self::STATUS_APPLIED;
    }

    /** @param array{id?: string, '$id'?: string, credits?: float, creditsUsed?: float, status?: string} $data */
    public static function fromArray(array $data): self
    {
        return new self($data['id'] ?? $data['$id'] ?? uniqid('credit_'), $data['credits'] ?? 0.0, $data['creditsUsed'] ?? 0.0, $data['status'] ?? self::STATUS_ACTIVE);
    }

    /** @return array{id: string, credits: float, creditsUsed: float, status: string} */
    public function toArray(): array
    {
        return ['id' => $this->id, 'credits' => $this->credits, 'creditsUsed' => $this->creditsUsed, 'status' => $this->status];
    }
}
