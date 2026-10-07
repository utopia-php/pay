<?php

declare(strict_types=1);

namespace Utopia\Pay;

final readonly class CardDetails
{
    public function __construct(private ?string $number = null, private ?int $expiryMonth = null, private ?int $expiryYear = null, private ?string $cvc = null)
    {
    }

    /** @return array{number?: string, exp_month?: int, exp_year?: int, cvc?: string} */
    public function toArray(): array
    {
        $details = [];
        if ($this->number !== null) {
            $details['number'] = $this->number;
        }
        if ($this->expiryMonth !== null) {
            $details['exp_month'] = $this->expiryMonth;
        }
        if ($this->expiryYear !== null) {
            $details['exp_year'] = $this->expiryYear;
        }
        if ($this->cvc !== null) {
            $details['cvc'] = $this->cvc;
        }
        return $details;
    }
}
