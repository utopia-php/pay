<?php

declare(strict_types=1);

namespace Utopia\Pay;

use Utopia\Pay\Exception\InvalidResponse;
use Utopia\Pay\Mandate\Status as MandateStatus;

final readonly class Mandate
{
    public function __construct(
        public string $id,
        public ?MandateStatus $status = null,
        public ?string $paymentMethodId = null,
    ) {
    }

    public static function fromPayload(Payload $data): self
    {
        return new self(
            id: $data->string('id') ?? throw new InvalidResponse(message: 'Missing processor object ID', code: 502),
            status: $data->enumValue('status', MandateStatus::class),
            paymentMethodId: $data->reference('payment_method'),
        );
    }
}
