<?php

namespace Utopia\Pay;

final readonly class Address
{
    public function __construct(
        private string $city,
        private string $country,
        private ?string $line1 = null,
        private ?string $line2 = null,
        private ?string $postalCode = null,
        private ?string $state = null,
    ) {
    }

    /**
     * Get Object as an array
     *
     * @return array<mixed>
     */
    public function asArray(): array
    {
        return [
            'city' => $this->city,
            'country' => $this->country,
            'line1' => $this->line1,
            'line2' => $this->line2,
            'postal_code' => $this->postalCode,
            'state' => $this->state,
        ];
    }
}
