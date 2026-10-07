<?php

declare(strict_types=1);

namespace Utopia\Pay;

final readonly class Dispute
{
    /** @param array<array-key, string> $metadata */
    public function __construct(
        public string $id,
        public ?string $paymentId = null,
        public array $metadata = [],
    ) {
    }

    public static function fromPayload(Payload $data): self
    {
        return new self(
            id: $data->string('id') ?? throw new Exception(message: 'Missing processor object ID', code: 502),
            paymentId: $data->reference('payment_intent'),
            metadata: $data->metadata(),
        );
    }
}
