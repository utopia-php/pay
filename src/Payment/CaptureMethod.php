<?php

declare(strict_types=1);

namespace Utopia\Pay\Payment;

enum CaptureMethod: string
{
    case Automatic = 'automatic';
    case AutomaticAsync = 'automatic_async';
    case Manual = 'manual';
}
