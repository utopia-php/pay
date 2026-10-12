<?php

declare(strict_types=1);

namespace Utopia\Pay\Invoice;

// Keep the persisted values while callers migrate to the invoice lifecycle.
enum State: string
{
    case Pending = 'pending';
    case Trial = 'trial';
    case Due = 'due';
    case Processing = 'processing';
    case RequiresAuthentication = 'requires_authentication';
    case Succeeded = 'succeeded';
    case Failed = 'failed';
    case Authorized = 'authorized';
    case Disputed = 'disputed';
    case Cancelled = 'cancelled';
    case Abandoned = 'abandoned';
    case Refunded = 'refunded';
}
