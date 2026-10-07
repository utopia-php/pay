<?php

declare(strict_types=1);

namespace Utopia\Pay;

use Utopia\Pay\Payment\CaptureMethod;
use Utopia\Pay\Payment\Status;

final readonly class Payment
{
    /** @param array<array-key, string> $metadata
     * @param list<Charge> $charges */
    public function __construct(
        public string $id,
        public int $amount = 0,
        public int $amountReceived = 0,
        public ?string $currency = null,
        public ?Status $status = null,
        public ?string $clientSecret = null,
        public ?string $paymentMethodId = null,
        public ?PaymentError $lastPaymentError = null,
        public array $metadata = [],
        public ?string $description = null,
        public ?CaptureMethod $captureMethod = null,
        public array $charges = [],
    ) {
    }

    public static function fromPayload(Payload $data): self
    {
        return new self(
            id: $data->string('id') ?? throw new Exception(message: 'Missing processor object ID', code: 502),
            amount: $data->integer('amount') ?? throw new Exception(message: 'Missing processor payment amount', code: 502),
            amountReceived: $data->integer('amount_received') ?? throw new Exception(message: 'Missing processor payment amount received', code: 502),
            currency: $data->string('currency') ?? throw new Exception(message: 'Missing processor payment currency', code: 502),
            status: $data->enumValue('status', Status::class) ?? throw new Exception(message: 'Missing processor payment status', code: 502),
            clientSecret: $data->string('client_secret'),
            paymentMethodId: $data->reference('payment_method'),
            lastPaymentError: ($object = $data->object('last_payment_error')) === null ? null : PaymentError::fromPayload($object),
            metadata: $data->metadata(),
            description: $data->string('description'),
            captureMethod: $data->enumValue('capture_method', CaptureMethod::class),
            charges: array_map(Charge::fromPayload(...), $data->object('charges')?->objects('data') ?? []),
        );
    }
}
