<?php

namespace Utopia\Pay\Discount;

final readonly class Discount
{
    public const TYPE_FIXED = 'fixed';

    public const TYPE_PERCENTAGE = 'percentage';

    public function __construct(
        private string $id,
        private float $value,
        private string $description = '',
        public string $type = self::TYPE_FIXED,
    ) {
        if ($value < 0) {
            throw new \InvalidArgumentException('Discount value cannot be negative');
        }
    }

    public function calculateDiscount(float $amount): float
    {
        if ($amount <= 0) {
            return 0;
        }

        return match ($this->type) {
            self::TYPE_FIXED => min($this->value, $amount),
            self::TYPE_PERCENTAGE => ($this->value / 100) * $amount,
            default => 0,
        };
    }

    /** @return array{id: string, value: float, description: string, type: string} */
    public function toArray(): array
    {
        return ['id' => $this->id, 'value' => $this->value, 'description' => $this->description, 'type' => $this->type];
    }

    /** @param array{id?: string, '$id'?: string, value?: float|null, description?: string, type?: string} $data */
    public static function fromArray(array $data): self
    {
        if (!isset($data['value'])) {
            throw new \InvalidArgumentException('Discount value cannot be null');
        }

        return new self($data['id'] ?? $data['$id'] ?? '', $data['value'], $data['description'] ?? '', $data['type'] ?? self::TYPE_FIXED);
    }
}
