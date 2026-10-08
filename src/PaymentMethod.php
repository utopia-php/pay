<?php

declare(strict_types=1);

namespace Utopia\Pay;

use Utopia\Pay\Exception\InvalidResponse;

final readonly class PaymentMethod
{
    public function __construct(
        public string $id,
        public ?Card $card = null,
        public ?Address $billingAddress = null,
    ) {
    }

    public static function fromPayload(Payload $data): self
    {
        return new self(
            id: $data->string('id') ?? throw new InvalidResponse(message: 'Missing processor object ID', code: 502),
            card: ($object = $data->object('card')) === null ? null : Card::fromPayload($object),
            billingAddress: ($address = $data->object('billing_details')?->object('address')) === null ? null : Address::fromPayload($address),
        );
    }
}
