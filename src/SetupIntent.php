<?php

declare(strict_types=1);

namespace Utopia\Pay;

use Utopia\Pay\Setup\Status;

final readonly class SetupIntent
{
    public function __construct(
        public string $id,
        public ?Status $status = null,
        public ?string $clientSecret = null,
        public ?string $paymentMethodId = null,
        public ?string $mandateId = null,
    ) {
    }

    public static function fromPayload(Payload $data): self
    {
        return new self(
            id: $data->string('id') ?? throw new Exception(message: 'Missing processor object ID', code: 502),
            status: $data->enumValue('status', Status::class),
            clientSecret: $data->string('client_secret'),
            paymentMethodId: $data->reference('payment_method'),
            mandateId: $data->reference('mandate'),
        );
    }
}
