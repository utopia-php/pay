<?php

namespace Utopia\Pay\PaymentMethod;

use Utopia\Pay\Address;

/**
 * Typed view of a payment method as returned by the adapter, e.g. PaymentMethod::fromArray($pay->getPaymentMethod(...)).
 */
class PaymentMethod
{
    public const TYPE_CARD = 'card';

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        private string $id,
        private string $type,
        private ?string $customerId = null,
        private ?string $brand = null,
        private ?string $last4 = null,
        private ?int $expMonth = null,
        private ?int $expYear = null,
        private ?string $funding = null,
        private ?string $country = null,
        private ?Address $billingAddress = null,
        private ?string $name = null,
        private ?string $email = null,
        private array $metadata = [],
        private ?int $createdAt = null,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getCustomerId(): ?string
    {
        return $this->customerId;
    }

    public function getBrand(): ?string
    {
        return $this->brand;
    }

    public function getLast4(): ?string
    {
        return $this->last4;
    }

    public function getExpMonth(): ?int
    {
        return $this->expMonth;
    }

    public function getExpYear(): ?int
    {
        return $this->expYear;
    }

    public function getFunding(): ?string
    {
        return $this->funding;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function getBillingAddress(): ?Address
    {
        return $this->billingAddress;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @return array<string, mixed>
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function getCreatedAt(): ?int
    {
        return $this->createdAt;
    }

    public function isCard(): bool
    {
        return $this->type === self::TYPE_CARD;
    }

    /**
     * Cards stay valid through the last day of their expiry month.
     */
    public function isExpired(?\DateTimeInterface $now = null): bool
    {
        if ($this->expMonth === null || $this->expYear === null) {
            return false;
        }

        $now ??= new \DateTimeImmutable();
        $current = (int) $now->format('Y') * 12 + (int) $now->format('n');

        return $current > $this->expYear * 12 + $this->expMonth;
    }

    /**
     * @param  array<string, mixed>  $data  Payment method payload
     */
    public static function fromArray(array $data): self
    {
        $type = (string) ($data['type'] ?? '');
        // Type-specific details live under a key named after the type, e.g. `card` or `sepa_debit`
        $details = $data[$type] ?? [];
        $billing = $data['billing_details'] ?? [];
        $address = $billing['address'] ?? [];
        $customer = $data['customer'] ?? null;

        return new self(
            id: (string) ($data['id'] ?? ''),
            type: $type,
            customerId: is_array($customer) ? ($customer['id'] ?? null) : $customer,
            brand: $details['brand'] ?? null,
            last4: $details['last4'] ?? null,
            expMonth: isset($details['exp_month']) ? (int) $details['exp_month'] : null,
            expYear: isset($details['exp_year']) ? (int) $details['exp_year'] : null,
            funding: $details['funding'] ?? null,
            country: $details['country'] ?? null,
            billingAddress: is_array($address) && array_filter($address) ? Address::fromArray($address) : null,
            name: $billing['name'] ?? null,
            email: $billing['email'] ?? null,
            metadata: $data['metadata'] ?? [],
            createdAt: isset($data['created']) ? (int) $data['created'] : null,
        );
    }
}
