<?php

declare(strict_types=1);

namespace Utopia\Pay\Adapter\Stripe\CardMandate;

enum AmountType: string
{
    case Fixed = 'fixed';
    case Maximum = 'maximum';
}
