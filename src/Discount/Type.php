<?php

declare(strict_types=1);

namespace Utopia\Pay\Discount;

enum Type: string
{
    case Fixed = 'fixed';
    case Percentage = 'percentage';
}
