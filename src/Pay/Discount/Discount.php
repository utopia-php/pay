<?php

namespace Utopia\Pay\Discount;

class Discount
{
    public const TYPE_FIXED = 'fixed'; // Fixed amount discount

    public const TYPE_PERCENTAGE = 'percentage'; // Percentage discount

    /**
     * @param  string  $id
     * @param  float  $value
     * @param  float  $amount
     * @param  string  $description
     * @param  string  $type
     */
    public function __construct(private string $id, private float $value, private float $amount, private string $description = '', private string $type = self::TYPE_FIXED)
    {
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

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getValue(): float
    {
        return $this->value;
    }

    public function setValue(float $value): static
    {
        $this->value = $value;

        return $this;
    }

    public function calculateDiscount(float $amount): float
    {
        if ($this->type === self::TYPE_FIXED) {
            return min($this->amount, $amount);
        } elseif ($this->type === self::TYPE_PERCENTAGE) {
            return ($this->value / 100) * $amount;
        }

        return 0;
    }

    public function isValid(): bool
    {
        return $this->amount > 0 && $this->type === self::TYPE_FIXED || $this->value > 0 && $this->type === self::TYPE_PERCENTAGE;
    }

    public function toArray()
    {
        return [
            'id' => $this->id,
            'amount' => $this->amount,
            'value' => $this->value,
            'description' => $this->description,
            'type' => $this->type,
        ];
    }

    public static function fromArray($data)
    {
        $discount = new self(
            $data['id'] ?? $data['$id'] ?? '',
            $data['value'] ?? 0,
            $data['amount'] ?? 0,
            $data['description'] ?? '',
            $data['type'] ?? self::TYPE_FIXED,
        );

        return $discount;
    }
}
