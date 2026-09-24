<?php

namespace Utopia\Pay\PaymentMethod;

use Utopia\Pay\Model;

/**
 * Payment method returned by createPaymentMethod(), getPaymentMethod(), listPaymentMethods() and the update calls
 */
class PaymentMethod extends Model
{
    public const TYPE_CARD = 'card';

    public function getId(): ?string
    {
        return $this->string('id');
    }

    public function getType(): ?string
    {
        return $this->string('type');
    }

    public function getCustomerId(): ?string
    {
        return $this->expandableId('customer');
    }

    public function getBrand(): ?string
    {
        return $this->string('card', 'brand');
    }

    public function getLast4(): ?string
    {
        return $this->string('card', 'last4');
    }

    public function getExpMonth(): ?int
    {
        return $this->int('card', 'exp_month');
    }

    public function getExpYear(): ?int
    {
        return $this->int('card', 'exp_year');
    }

    public function getFunding(): ?string
    {
        return $this->string('card', 'funding');
    }

    /**
     * Country that issued the card
     */
    public function getCountry(): ?string
    {
        return $this->string('card', 'country');
    }

    public function getBillingName(): ?string
    {
        return $this->string('billing_details', 'name');
    }

    public function getBillingEmail(): ?string
    {
        return $this->string('billing_details', 'email');
    }

    public function getBillingPhone(): ?string
    {
        return $this->string('billing_details', 'phone');
    }

    public function getBillingCity(): ?string
    {
        return $this->string('billing_details', 'address', 'city');
    }

    public function getBillingCountry(): ?string
    {
        return $this->string('billing_details', 'address', 'country');
    }

    public function getBillingLine1(): ?string
    {
        return $this->string('billing_details', 'address', 'line1');
    }

    public function getBillingLine2(): ?string
    {
        return $this->string('billing_details', 'address', 'line2');
    }

    public function getBillingPostalCode(): ?string
    {
        return $this->string('billing_details', 'address', 'postal_code');
    }

    public function getBillingState(): ?string
    {
        return $this->string('billing_details', 'address', 'state');
    }

    /**
     * @return array<string, mixed>
     */
    public function getMetadata(): array
    {
        return $this->array('metadata') ?? [];
    }

    public function getCreatedAt(): ?int
    {
        return $this->int('created');
    }

    public function isCard(): bool
    {
        return $this->getType() === self::TYPE_CARD;
    }

    /**
     * Cards stay valid through the last day of their expiry month.
     */
    public function isExpired(?\DateTimeInterface $now = null): bool
    {
        $month = $this->getExpMonth();
        $year = $this->getExpYear();
        if ($month === null || $year === null) {
            return false;
        }

        $now ??= new \DateTimeImmutable();
        $current = (int) $now->format('Y') * 12 + (int) $now->format('n');

        return $current > $year * 12 + $month;
    }
}
