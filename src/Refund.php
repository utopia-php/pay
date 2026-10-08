<?php

declare(strict_types=1);

namespace Utopia\Pay;

use Utopia\Pay\Exception\InvalidResponse;
use Utopia\Pay\Refund\Status;

final readonly class Refund
{
    public function __construct(
        public string $id,
        public int $amount = 0,
        public ?Status $status = null,
    ) {
    }

    public static function fromPayload(Payload $data): self
    {
        return new self(
            id: $data->string('id') ?? throw new InvalidResponse(message: 'Missing processor object ID', code: 502),
            amount: $data->integer('amount') ?? 0,
            status: $data->enumValue('status', Status::class),
        );
    }
}
