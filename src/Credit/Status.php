<?php

declare(strict_types=1);

namespace Utopia\Pay\Credit;

enum Status: string
{
    case Active = 'active';
    case Applied = 'applied';
    case Expired = 'expired';
}
