<?php

declare(strict_types=1);

namespace Utopia\Pay\Invoice;

enum Status: string
{
    case Pending = 'pending';
    case Due = 'due';
    case Refunded = 'refunded';
    case Cancelled = 'cancelled';
    case Succeeded = 'succeeded';
    case Processing = 'processing';
    case Failed = 'failed';
}
