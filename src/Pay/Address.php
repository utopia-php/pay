<?php

namespace Utopia\Pay;

class Address
{
    /**
     * City, district, suburb, town or village
     *
     * @var string
     */
    protected string $city;

    /**
     * Two letter country code
     * https://en.wikipedia.org/wiki/ISO_3166-1_alpha-2
     *
     * @var string
     */
    protected string $country;

    /**
     * Address Line 1 (eg. street, PO Box, or company name)
     *
     * @var string|null
     */
    protected ?string $line1;

    /**
     * Address Line 2 (eg, appartment, suite, unit or building)
     *
     * @var string|null
     */
    protected ?string $line2;

    /**
     * ZIP or postal code
     *
     * @var string|null
     */
    protected ?string $postalCode;

    /**
     * State, county, province or region
     *
     * @var string|null
     */
    protected ?string $state;

    public function __construct(string $city, string $country, string $line1 = null, string $line2 = null, string $postalCode = null, string $state = null)
    {
        $this->city = $city;
        $this->country = $country;
        $this->line1 = $line1;
        $this->line2 = $line2;
        $this->postalCode = $postalCode;
        $this->state = $state;
    }

    /**
     * Get the value of city
     *
     * @return string|null
     */
    public function getCity(): ?string
    {
        return $this->city ?? null;
    }

    /**
     * Set the value of city
     *
     * @param  string  $city
     * @return self
     */
    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get the value of country
     *
     * @return string
     */
    public function getCountry(): string
    {
        return $this->country;
    }

    /**
     * Set the value of country
     *
     * @param  string  $country
     * @return self
     */
    public function setCountry(string $country): self
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Get the value of line1
     *
     * @return string|null
     */
    public function getLine1(): ?string
    {
        return $this->line1 ?? null;
    }

    /**
     * Set the value of line1
     *
     * @param  string  $line1
     * @return self
     */
    public function setLine1(string $line1): self
    {
        $this->line1 = $line1;

        return $this;
    }

    /**
     * Get the value of line2
     *
     * @return string|null
     */
    public function getLine2(): ?string
    {
        return $this->line2 ?? null;
    }

    /**
     * Set the value of line2
     *
     * @param  string  $line2
     * @return self
     */
    public function setLine2(string $line2): self
    {
        $this->line2 = $line2;

        return $this;
    }

    /**
     * Get the value of postalCode
     *
     * @return string|null
     */
    public function getPostalCode(): ?string
    {
        return $this->postalCode ?? null;
    }

    /**
     * Set the value of postalCode
     *
     * @param  string  $postalCode
     * @return self
     */
    public function setPostalCode(string $postalCode): self
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    /**
     * Get the value of state
     *
     * @return string|null
     */
    public function getState(): ?string
    {
        return $this->state ?? null;
    }

    /**
     * Set the value of state
     *
     * @param  string  $state
     * @return self
     */
    public function setState(string $state): self
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get Object as an array (snake_case keys for API compatibility).
     *
     * @return array<string, string|null>
     *
     * @deprecated Use toArray() instead
     */
    public function asArray(): array
    {
        return [
            'city' => $this->city ?? null,
            'country' => $this->country ?? null,
            'line1' => $this->line1 ?? null,
            'line2' => $this->line2 ?? null,
            'postal_code' => $this->postalCode ?? null,
            'state' => $this->state ?? null,
        ];
    }

    /**
     * Convert the address to an array representation.
     *
     * @return array<string, string|null> The address data as an array
     */
    public function toArray(): array
    {
        return [
            'city' => $this->city ?? null,
            'country' => $this->country ?? null,
            'line1' => $this->line1 ?? null,
            'line2' => $this->line2 ?? null,
            'postalCode' => $this->postalCode ?? null,
            'state' => $this->state ?? null,
        ];
    }

    /**
     * Create an Address instance from an array.
     *
     * @param  array<string, string|null>  $data  The address data array
     * @return self The created Address instance
     */
    public static function fromArray(array $data): self
    {
        return new self(
            city: $data['city'] ?? '',
            country: $data['country'] ?? '',
            line1: $data['line1'] ?? null,
            line2: $data['line2'] ?? null,
            postalCode: $data['postalCode'] ?? $data['postal_code'] ?? null,
            state: $data['state'] ?? null
        );
    }

    /**
     * Check if the address is complete (has all required fields).
     *
     * @return bool True if city and country are set
     */
    public function isComplete(): bool
    {
        return ! empty($this->city) && ! empty($this->country);
    }

    /**
     * Check if the address is empty.
     *
     * @return bool True if all fields are empty
     */
    public function isEmpty(): bool
    {
        return empty($this->city)
            && empty($this->country)
            && empty($this->line1)
            && empty($this->line2)
            && empty($this->postalCode)
            && empty($this->state);
    }
}
