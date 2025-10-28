<?php

namespace Utopia\Pay\Discount;

/**
 * Discount class for managing invoice discounts.
 *
 * Supports both fixed amount and percentage-based discounts.
 * Discounts are applied sequentially to invoice amounts.
 */
class Discount
{
    /**
     * Fixed amount discount type (e.g., $10 off).
     */
    public const TYPE_FIXED = 'fixed'; // Fixed amount discount

    /**
     * Percentage discount type (e.g., 15% off).
     */
    public const TYPE_PERCENTAGE = 'percentage'; // Percentage discount

    /**
     * Create a new Discount instance.
     *
     * @param  string  $id  Unique identifier for the discount
     * @param  float  $value  The discount value - either a fixed amount (e.g., 10.00) or percentage (e.g., 15 for 15%)
     * @param  string  $description  Optional description of the discount
     * @param  string  $type  The discount type (TYPE_FIXED or TYPE_PERCENTAGE)
     *
     * @throws \InvalidArgumentException If discount value is negative
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

    /**
     * Get the discount ID.
     *
     * @return string The unique discount identifier
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Set the discount ID.
     *
     * @param  string  $id  The discount ID
     * @return static
     */
    public function setId(string $id): static
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the discount value (either fixed amount or percentage based on type).
     *
     * @return float The discount value
     */
    public function getValue(): float
    {
        return $this->value;
    }

    /**
     * Set the discount value (either fixed amount or percentage based on type).
     *
     * @param  float  $value  The discount value
     * @return static
     *
     * @throws \InvalidArgumentException If value is negative
     */
    public function setValue(float $value): static
    {
        if ($value < 0) {
            throw new \InvalidArgumentException('Discount value cannot be negative');
        }

        $this->value = $value;

        return $this;
    }

    /**
     * Get the discount description.
     *
     * @return string The description text
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Set the discount description.
     *
     * @param  string  $description  The description text
     * @return static
     */
    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get the discount type.
     *
     * @return string The discount type (TYPE_FIXED or TYPE_PERCENTAGE)
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Set the discount type.
     *
     * @param  string  $type  The discount type (use TYPE_* constants)
     * @return static
     */
    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Calculate the discount amount to apply to a given amount.
     *
     * For TYPE_FIXED: Returns the discount value or the amount, whichever is smaller.
     * For TYPE_PERCENTAGE: Returns the percentage of the amount.
     *
     * @param  float  $amount  The original amount/subtotal to calculate the discount from
     * @return float The calculated discount amount (never exceeds the input amount)
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

    /**
     * Convert the discount to an array representation.
     *
     * @return array The discount data as an array
     */
    public function toArray()
    {
        return [
            'id' => $this->id,
            'value' => $this->value,
            'description' => $this->description,
            'type' => $this->type,
        ];
    }

    /**
     * Create a Discount instance from an array.
     *
     * @param  array  $data  The discount data array
     * @return self The created Discount instance
     *
     * @throws \InvalidArgumentException If value is null or negative
     */
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
