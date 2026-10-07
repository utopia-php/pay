<?php

declare(strict_types=1);

namespace Utopia\Pay\Setup;

enum Status: string
{
    case RequiresPaymentMethod = 'requires_payment_method';
    case RequiresConfirmation = 'requires_confirmation';
    case RequiresAction = 'requires_action';
    case Processing = 'processing';
    case Canceled = 'canceled';
    case Succeeded = 'succeeded';
}
