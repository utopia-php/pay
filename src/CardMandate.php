<?php

declare(strict_types=1);

namespace Utopia\Pay;

final readonly class CardMandate
{
    public function __construct(public string $reference, public int $amount, public string $currency, public int $startDate, public string $description = '')
    {
    }
}
