<?php

namespace Utopia\Pay\Discount;

class Discount
{
    public const TYPE_FIXED = 'fixed'; // Fixed amount discount

    public const TYPE_PERCENTAGE = 'percentage'; // Percentage discount

    /**
     * @param  string  $id  Unique identifier for the discount
     * @param  float  $value  The discount value - either a fixed amount (e.g., 10.00) or percentage (e.g., 15 for 15%) depending on $type
     * @param  string  $description  Optional description of the discount
     * @param  string  $type  The discount type (TYPE_FIXED or TYPE_PERCENTAGE)
     */
    public function __construct(
        private string $id,
        private float $value,
        private string $description = '',
        private string $type = self::TYPE_FIXED
    ) {
        if ($this->value < 0) {
            throw new \InvalidArgumentException('Discount value cannot be negative');
        }
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

    /**
     * Get the discount value (either fixed amount or percentage based on type)
     */
    public function getValue(): float
    {
        return $this->value;
    }

    /**
     * Set the discount value (either fixed amount or percentage based on type)
     *
     * @throws \InvalidArgumentException if value is negative
     */
    public function setValue(float $value): static
    {
        if ($value < 0) {
            throw new \InvalidArgumentException('Discount value cannot be negative');
        }

        $this->value = $value;

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

    /**
     * Calculate the discount amount to apply
     *
     * @param  float  $amount  The original amount/subtotal to calculate the discount from
     * @return float The calculated discount amount
     */
    public function calculateDiscount(float $amount): float
    {
        if ($amount <= 0) {
            return 0;
        }

        if ($this->type === self::TYPE_FIXED) {
            return min($this->value, $amount);
        } elseif ($this->type === self::TYPE_PERCENTAGE) {
            return ($this->value / 100) * $amount;
        }

        return 0;
    }

    public function toArray()
    {
        return [
            'id' => $this->id,
            'value' => $this->value,
            'description' => $this->description,
            'type' => $this->type,
        ];
    }

    public static function fromArray($data)
    {
        $value = $data['value'] ?? null;

        if ($value === null) {
            throw new \InvalidArgumentException('Discount value cannot be null');
        }
        if ($value < 0) {
            throw new \InvalidArgumentException('Discount value cannot be negative');
        }

        $discount = new self(
            $data['id'] ?? $data['$id'] ?? '',
            $value,
            $data['description'] ?? '',
            $data['type'] ?? self::TYPE_FIXED,
        );

        return $discount;
    }
}
