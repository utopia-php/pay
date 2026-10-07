<?php

declare(strict_types=1);

namespace Utopia\Pay;

final readonly class Card
{
    public function __construct(
        public ?string $brand = null,
        public ?string $country = null,
        public ?int $expiryMonth = null,
        public ?int $expiryYear = null,
        public ?string $last4 = null,
    ) {
    }

    public static function fromPayload(Payload $data): self
    {
        return new self(
            brand: $data->string('brand'),
            country: $data->string('country'),
            expiryMonth: $data->integer('exp_month'),
            expiryYear: $data->integer('exp_year'),
            last4: $data->string('last4'),
        );
    }
}
