<?php

declare(strict_types=1);

namespace Utopia\Pay\Payment;

enum Status: string
{
    case RequiresPaymentMethod = 'requires_payment_method';
    case RequiresConfirmation = 'requires_confirmation';
    case RequiresAction = 'requires_action';
    case Processing = 'processing';
    case RequiresCapture = 'requires_capture';
    case Canceled = 'canceled';
    case Succeeded = 'succeeded';
}
