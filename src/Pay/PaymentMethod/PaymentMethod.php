<?php

namespace Utopia\Pay\PaymentMethod;

use Utopia\Pay\Address;

/**
 * PaymentMethod class for managing payment method data.
 *
 * Represents a payment method (card, bank account, etc.) attached to a customer.
 */
class PaymentMethod
{
    /**
     * Card payment method type.
     */
    public const TYPE_CARD = 'card';

    /**
     * Bank account payment method type.
     */
    public const TYPE_BANK_ACCOUNT = 'bank_account';

    /**
     * SEPA debit payment method type.
     */
    public const TYPE_SEPA_DEBIT = 'sepa_debit';

    /**
     * ACH debit payment method type.
     */
    public const TYPE_ACH_DEBIT = 'us_bank_account';

    /**
     * PayPal payment method type.
     */
    public const TYPE_PAYPAL = 'paypal';

    /**
     * Create a new PaymentMethod instance.
     *
     * @param  string  $id  Unique identifier for the payment method
     * @param  string  $type  Payment method type (card, bank_account, etc.)
     * @param  string|null  $customerId  The customer this payment method belongs to
     * @param  string|null  $brand  Card brand (visa, mastercard, etc.) for card types
     * @param  string|null  $last4  Last 4 digits of card or account number
     * @param  int|null  $expMonth  Card expiration month (1-12)
     * @param  int|null  $expYear  Card expiration year (4 digits)
     * @param  string|null  $funding  Card funding type (credit, debit, prepaid)
     * @param  string|null  $country  Country code of the card issuer
     * @param  Address|null  $billingAddress  Billing address associated with the payment method
     * @param  string|null  $name  Cardholder or account holder name
     * @param  string|null  $email  Email associated with the payment method
     * @param  string|null  $phone  Phone number associated with the payment method
     * @param  array<string, mixed>  $metadata  Additional metadata
     * @param  int|null  $createdAt  Unix timestamp when payment method was created
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
        private ?string $phone = null,
        private array $metadata = [],
        private ?int $createdAt = null
    ) {
        $this->createdAt = $createdAt ?? time();
    }

    /**
     * Get the payment method ID.
     *
     * @return string The unique payment method identifier
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Set the payment method ID.
     *
     * @param  string  $id  The payment method ID
     * @return static
     */
    public function setId(string $id): static
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the payment method type.
     *
     * @return string The payment method type
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Set the payment method type.
     *
     * @param  string  $type  The payment method type
     * @return static
     */
    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get the customer ID.
     *
     * @return string|null The customer ID
     */
    public function getCustomerId(): ?string
    {
        return $this->customerId;
    }

    /**
     * Set the customer ID.
     *
     * @param  string|null  $customerId  The customer ID
     * @return static
     */
    public function setCustomerId(?string $customerId): static
    {
        $this->customerId = $customerId;

        return $this;
    }

    /**
     * Get the card brand.
     *
     * @return string|null The card brand (visa, mastercard, etc.)
     */
    public function getBrand(): ?string
    {
        return $this->brand;
    }

    /**
     * Set the card brand.
     *
     * @param  string|null  $brand  The card brand
     * @return static
     */
    public function setBrand(?string $brand): static
    {
        $this->brand = $brand;

        return $this;
    }

    /**
     * Get the last 4 digits.
     *
     * @return string|null The last 4 digits
     */
    public function getLast4(): ?string
    {
        return $this->last4;
    }

    /**
     * Set the last 4 digits.
     *
     * @param  string|null  $last4  The last 4 digits
     * @return static
     */
    public function setLast4(?string $last4): static
    {
        $this->last4 = $last4;

        return $this;
    }

    /**
     * Get the expiration month.
     *
     * @return int|null The expiration month (1-12)
     */
    public function getExpMonth(): ?int
    {
        return $this->expMonth;
    }

    /**
     * Set the expiration month.
     *
     * @param  int|null  $expMonth  The expiration month
     * @return static
     */
    public function setExpMonth(?int $expMonth): static
    {
        $this->expMonth = $expMonth;

        return $this;
    }

    /**
     * Get the expiration year.
     *
     * @return int|null The expiration year (4 digits)
     */
    public function getExpYear(): ?int
    {
        return $this->expYear;
    }

    /**
     * Set the expiration year.
     *
     * @param  int|null  $expYear  The expiration year
     * @return static
     */
    public function setExpYear(?int $expYear): static
    {
        $this->expYear = $expYear;

        return $this;
    }

    /**
     * Get the card funding type.
     *
     * @return string|null The funding type (credit, debit, prepaid)
     */
    public function getFunding(): ?string
    {
        return $this->funding;
    }

    /**
     * Set the card funding type.
     *
     * @param  string|null  $funding  The funding type
     * @return static
     */
    public function setFunding(?string $funding): static
    {
        $this->funding = $funding;

        return $this;
    }

    /**
     * Get the country code.
     *
     * @return string|null The country code
     */
    public function getCountry(): ?string
    {
        return $this->country;
    }

    /**
     * Set the country code.
     *
     * @param  string|null  $country  The country code
     * @return static
     */
    public function setCountry(?string $country): static
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Get the billing address.
     *
     * @return Address|null The billing address
     */
    public function getBillingAddress(): ?Address
    {
        return $this->billingAddress;
    }

    /**
     * Set the billing address.
     *
     * @param  Address|null  $billingAddress  The billing address
     * @return static
     */
    public function setBillingAddress(?Address $billingAddress): static
    {
        $this->billingAddress = $billingAddress;

        return $this;
    }

    /**
     * Get the cardholder name.
     *
     * @return string|null The name
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Set the cardholder name.
     *
     * @param  string|null  $name  The name
     * @return static
     */
    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the email.
     *
     * @return string|null The email
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * Set the email.
     *
     * @param  string|null  $email  The email
     * @return static
     */
    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get the phone number.
     *
     * @return string|null The phone number
     */
    public function getPhone(): ?string
    {
        return $this->phone;
    }

    /**
     * Set the phone number.
     *
     * @param  string|null  $phone  The phone number
     * @return static
     */
    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get the metadata.
     *
     * @return array<string, mixed> The metadata
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    /**
     * Set the metadata.
     *
     * @param  array<string, mixed>  $metadata  The metadata
     * @return static
     */
    public function setMetadata(array $metadata): static
    {
        $this->metadata = $metadata;

        return $this;
    }

    /**
     * Get the creation timestamp.
     *
     * @return int|null The creation timestamp
     */
    public function getCreatedAt(): ?int
    {
        return $this->createdAt;
    }

    /**
     * Set the creation timestamp.
     *
     * @param  int|null  $createdAt  The creation timestamp
     * @return static
     */
    public function setCreatedAt(?int $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Check if this is a card payment method.
     *
     * @return bool True if type is card
     */
    public function isCard(): bool
    {
        return $this->type === self::TYPE_CARD;
    }

    /**
     * Check if the card is expired.
     *
     * @return bool True if card is expired
     */
    public function isExpired(): bool
    {
        if ($this->expMonth === null || $this->expYear === null) {
            return false;
        }

        $now = new \DateTime();
        $expDate = \DateTime::createFromFormat('Y-n', $this->expYear.'-'.$this->expMonth);

        if ($expDate === false) {
            return false;
        }

        // Card is valid through the end of the expiration month
        $expDate->modify('last day of this month');

        return $now > $expDate;
    }

    /**
     * Get a display string for the payment method.
     *
     * @return string A human-readable display string (e.g., "Visa ending in 4242")
     */
    public function getDisplayString(): string
    {
        if ($this->isCard() && $this->brand && $this->last4) {
            return ucfirst($this->brand).' ending in '.$this->last4;
        }

        if ($this->last4) {
            return ucfirst($this->type).' ending in '.$this->last4;
        }

        return ucfirst($this->type);
    }

    /**
     * Convert the payment method to an array representation.
     *
     * @return array<string, mixed> The payment method data as an array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'customerId' => $this->customerId,
            'brand' => $this->brand,
            'last4' => $this->last4,
            'expMonth' => $this->expMonth,
            'expYear' => $this->expYear,
            'funding' => $this->funding,
            'country' => $this->country,
            'billingAddress' => $this->billingAddress?->toArray(),
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'metadata' => $this->metadata,
            'createdAt' => $this->createdAt,
        ];
    }

    /**
     * Create a PaymentMethod instance from an array.
     *
     * @param  array<string, mixed>  $data  The payment method data array
     * @return self The created PaymentMethod instance
     */
    public static function fromArray(array $data): self
    {
        $billingAddress = null;
        if (isset($data['billingAddress']) && is_array($data['billingAddress'])) {
            $billingAddress = Address::fromArray($data['billingAddress']);
        } elseif (isset($data['billing_details']['address']) && is_array($data['billing_details']['address'])) {
            $billingAddress = Address::fromArray($data['billing_details']['address']);
        }

        // Handle card-specific data from various formats
        $cardData = $data['card'] ?? $data;
        $billingDetails = $data['billing_details'] ?? [];

        return new self(
            id: $data['id'] ?? $data['$id'] ?? uniqid('pm_'),
            type: $data['type'] ?? self::TYPE_CARD,
            customerId: $data['customerId'] ?? $data['customer'] ?? null,
            brand: $cardData['brand'] ?? $data['brand'] ?? null,
            last4: $cardData['last4'] ?? $data['last4'] ?? null,
            expMonth: isset($cardData['exp_month']) ? (int) $cardData['exp_month'] : ($data['expMonth'] ?? null),
            expYear: isset($cardData['exp_year']) ? (int) $cardData['exp_year'] : ($data['expYear'] ?? null),
            funding: $cardData['funding'] ?? $data['funding'] ?? null,
            country: $cardData['country'] ?? $data['country'] ?? null,
            billingAddress: $billingAddress,
            name: $billingDetails['name'] ?? $data['name'] ?? null,
            email: $billingDetails['email'] ?? $data['email'] ?? null,
            phone: $billingDetails['phone'] ?? $data['phone'] ?? null,
            metadata: $data['metadata'] ?? [],
            createdAt: $data['createdAt'] ?? $data['created'] ?? null
        );
    }
}
