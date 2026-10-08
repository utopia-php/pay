<?php

declare(strict_types=1);

namespace Utopia\Pay\Adapter\Stripe;

enum ErrorType: string
{
    case Api = 'api_error';
    case Card = 'card_error';
    case Idempotency = 'idempotency_error';
    case InvalidRequest = 'invalid_request_error';
}
