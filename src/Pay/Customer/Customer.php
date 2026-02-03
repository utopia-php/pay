<?php

namespace Utopia\Pay\Customer;

use Utopia\Pay\Address;

/**
 * Customer class for managing payment customer data.
 *
 * Represents a customer in the payment system with their contact
 * and billing information.
 */
class Customer
{
    /**
     * Create a new Customer instance.
     *
     * @param  string  $id  Unique identifier for the customer
     * @param  string  $name  Customer's full name
     * @param  string  $email  Customer's email address
     * @param  Address|null  $address  Customer's billing address
     * @param  string|null  $phone  Customer's phone number
     * @param  string|null  $defaultPaymentMethod  Default payment method ID
     * @param  array<string, mixed>  $metadata  Additional metadata
     * @param  int|null  $createdAt  Unix timestamp when customer was created
     */
    public function __construct(
        private string $id,
        private string $name,
        private string $email,
        private ?Address $address = null,
        private ?string $phone = null,
        private ?string $defaultPaymentMethod = null,
        private array $metadata = [],
        private ?int $createdAt = null
    ) {
        $this->createdAt = $createdAt ?? time();
    }

    /**
     * Get the customer ID.
     *
     * @return string The unique customer identifier
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Set the customer ID.
     *
     * @param  string  $id  The customer ID
     * @return static
     */
    public function setId(string $id): static
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the customer's name.
     *
     * @return string The customer's full name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set the customer's name.
     *
     * @param  string  $name  The customer's full name
     * @return static
     */
    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the customer's email.
     *
     * @return string The customer's email address
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * Set the customer's email.
     *
     * @param  string  $email  The customer's email address
     * @return static
     */
    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get the customer's address.
     *
     * @return Address|null The billing address or null if not set
     */
    public function getAddress(): ?Address
    {
        return $this->address;
    }

    /**
     * Set the customer's address.
     *
     * @param  Address|null  $address  The billing address
     * @return static
     */
    public function setAddress(?Address $address): static
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get the customer's phone number.
     *
     * @return string|null The phone number or null if not set
     */
    public function getPhone(): ?string
    {
        return $this->phone;
    }

    /**
     * Set the customer's phone number.
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
     * Get the default payment method ID.
     *
     * @return string|null The default payment method ID or null if not set
     */
    public function getDefaultPaymentMethod(): ?string
    {
        return $this->defaultPaymentMethod;
    }

    /**
     * Set the default payment method ID.
     *
     * @param  string|null  $defaultPaymentMethod  The payment method ID
     * @return static
     */
    public function setDefaultPaymentMethod(?string $defaultPaymentMethod): static
    {
        $this->defaultPaymentMethod = $defaultPaymentMethod;

        return $this;
    }

    /**
     * Get the metadata.
     *
     * @return array<string, mixed> The metadata array
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    /**
     * Set the metadata.
     *
     * @param  array<string, mixed>  $metadata  The metadata array
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
     * @return int|null Unix timestamp when customer was created
     */
    public function getCreatedAt(): ?int
    {
        return $this->createdAt;
    }

    /**
     * Set the creation timestamp.
     *
     * @param  int|null  $createdAt  Unix timestamp
     * @return static
     */
    public function setCreatedAt(?int $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Check if the customer has an address.
     *
     * @return bool True if address is set
     */
    public function hasAddress(): bool
    {
        return $this->address !== null;
    }

    /**
     * Check if the customer has a default payment method.
     *
     * @return bool True if default payment method is set
     */
    public function hasDefaultPaymentMethod(): bool
    {
        return $this->defaultPaymentMethod !== null;
    }

    /**
     * Convert the customer to an array representation.
     *
     * @return array<string, mixed> The customer data as an array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'address' => $this->address?->toArray(),
            'phone' => $this->phone,
            'defaultPaymentMethod' => $this->defaultPaymentMethod,
            'metadata' => $this->metadata,
            'createdAt' => $this->createdAt,
        ];
    }

    /**
     * Create a Customer instance from an array.
     *
     * @param  array<string, mixed>  $data  The customer data array
     * @return self The created Customer instance
     */
    public static function fromArray(array $data): self
    {
        $address = null;
        if (isset($data['address']) && is_array($data['address'])) {
            $address = Address::fromArray($data['address']);
        }

        return new self(
            id: $data['id'] ?? $data['$id'] ?? uniqid('cus_'),
            name: $data['name'] ?? '',
            email: $data['email'] ?? '',
            address: $address,
            phone: $data['phone'] ?? null,
            defaultPaymentMethod: $data['defaultPaymentMethod'] ?? $data['default_payment_method'] ?? null,
            metadata: $data['metadata'] ?? [],
            createdAt: $data['createdAt'] ?? $data['created'] ?? null
        );
    }
}
