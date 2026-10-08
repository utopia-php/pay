<?php

declare(strict_types=1);

namespace Utopia\Pay\Adapter\Stripe\CardMandate;

enum Interval: string
{
    case Day = 'day';
    case Month = 'month';
    case Sporadic = 'sporadic';
    case Week = 'week';
    case Year = 'year';
}
