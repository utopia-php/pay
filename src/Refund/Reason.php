<?php

declare(strict_types=1);

namespace Utopia\Pay\Refund;

enum Reason: string
{
    case Duplicate = 'duplicate';
    case Fraudulent = 'fraudulent';
    case RequestedByCustomer = 'requested_by_customer';
}
