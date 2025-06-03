<?php

namespace Pay\Discount;

class Discount
{
    public const TYPE_FIXED = 'fixed'; // Fixed amount discount
    public const TYPE_PERCENTAGE = 'percentage'; // Percentage discount

    public function __construct(private string $id, private float $value, private float $amount, private string $description = '', private string $type =  self::TYPE_FIXED)
    {}

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function getAmount()
    {
        return $this->amount;
    }

    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setValue(float $value)
    {
        $this->value = $value;
        return $this;
    }

    public function isValid()
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
            'type' => $this->type
        ];
    }

    public static function fromArray($data)
    {
        $discount = new self(
            $data['id'] ?? $data['$id'] ?? '',
            $data['amount'] ?? 0,
            $data['description'] ?? '',
            $data['type'] ?? self::TYPE_FIXED,
            $data['id'] ?? null
        );

        return $discount;
    }
}