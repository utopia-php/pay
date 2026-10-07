<?php

declare(strict_types=1);

namespace Utopia\Pay\Refund;

enum Status: string
{
    case Pending = 'pending';
    case RequiresAction = 'requires_action';
    case Succeeded = 'succeeded';
    case Failed = 'failed';
    case Canceled = 'canceled';
}
