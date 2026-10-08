<?php

declare(strict_types=1);

namespace Utopia\Pay;

use Utopia\Pay\Exception\InvalidResponse;

final readonly class Customer
{
    public function __construct(
        public string $id,
        public ?string $name = null,
        public ?string $email = null,
        public bool $deleted = false,
    ) {
    }

    public static function fromPayload(Payload $data): self
    {
        return new self(
            id: $data->string('id') ?? throw new InvalidResponse(message: 'Missing processor object ID', code: 502),
            name: $data->string('name'),
            email: $data->string('email'),
            deleted: $data->boolean('deleted') ?? false,
        );
    }
}
