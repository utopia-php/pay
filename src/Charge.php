<?php

declare(strict_types=1);

namespace Utopia\Pay;

final readonly class Charge
{
    public function __construct(
        public string $id,
        public int $amountRefunded = 0,
    ) {
    }

    public static function fromPayload(Payload $data): self
    {
        return new self(
            id: $data->string('id') ?? throw new Exception(message: 'Missing processor object ID', code: 502),
            amountRefunded: $data->integer('amount_refunded') ?? 0,
        );
    }
}
