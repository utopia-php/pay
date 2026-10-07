<?php

declare(strict_types=1);

namespace Utopia\Pay\Discount;

final readonly class Discount
{
    public function __construct(
        private string $id,
        private float $value,
        private string $description = '',
        public Type $type = Type::Fixed,
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
            Type::Fixed => min($this->value, $amount),
            Type::Percentage => ($this->value / 100) * $amount,
        };
    }

    /** @return array{id: string, value: float, description: string, type: string} */
    public function toArray(): array
    {
        return ['id' => $this->id, 'value' => $this->value, 'description' => $this->description, 'type' => $this->type->value];
    }

    /** @param array{id?: string, '$id'?: string, value?: float|null, description?: string, type?: string} $data */
    public static function fromArray(array $data): self
    {
        if (!isset($data['value'])) {
            throw new \InvalidArgumentException('Discount value cannot be null');
        }

        return new self($data['id'] ?? $data['$id'] ?? '', $data['value'], $data['description'] ?? '', isset($data['type']) ? Type::from($data['type']) : Type::Fixed);
    }
}
