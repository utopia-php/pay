<?php

declare(strict_types=1);

namespace Utopia\Pay;

final readonly class Address
{
    public function __construct(
        public string $city,
        private string $country,
        private ?string $line1 = null,
        private ?string $line2 = null,
        public ?string $postalCode = null,
        private ?string $state = null,
    ) {
    }

    public static function fromPayload(Payload $data): self
    {
        return new self($data->string('city') ?? '', $data->string('country') ?? '', $data->string('line1'), $data->string('line2'), $data->string('postal_code'), $data->string('state'));
    }

    /**
     * Get Object as an array
     *
     * @return array{city: string, country: string, line1: ?string, line2: ?string, postal_code: ?string, state: ?string}
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
