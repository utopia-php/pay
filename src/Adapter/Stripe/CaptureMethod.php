<?php

declare(strict_types=1);

namespace Utopia\Pay\Adapter\Stripe;

enum CaptureMethod: string
{
    case Automatic = 'automatic';
    case AutomaticAsync = 'automatic_async';
    case Manual = 'manual';
}
