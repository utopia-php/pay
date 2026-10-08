<?php

declare(strict_types=1);

namespace Utopia\Pay;

final readonly class PaymentError
{
    public function __construct(
        public ?string $code = null,
        public ?string $declineCode = null,
        public ?string $adviceCode = null,
        public ?string $type = null,
        public ?string $message = null,
        public ?string $paymentMethodId = null,
        public ?Payment $payment = null,
        public ?string $paymentId = null,
    ) {
    }

    public static function fromPayload(Payload $data): self
    {
        return new self(
            code: $data->string('code'),
            declineCode: $data->string('decline_code'),
            adviceCode: $data->string('advice_code'),
            type: $data->string('type'),
            message: $data->string('message'),
            paymentMethodId: $data->reference('payment_method'),
            payment: ($object = $data->expandedObject('payment_intent')) === null ? null : Payment::fromPayload($object),
            paymentId: $data->reference('payment_intent'),
        );
    }
}
