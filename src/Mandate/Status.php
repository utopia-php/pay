<?php

declare(strict_types=1);

namespace Utopia\Pay\Mandate;

enum Status: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Pending = 'pending';
}
