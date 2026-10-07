<?php

declare(strict_types=1);

namespace Utopia\Pay;

/** @phpstan-type CardOptions array{card: array{mandate_options: array{reference: string, description: string, amount: int, currency: string, start_date: int, amount_type: string, interval: string, supported_types: list<string>}}} */
final readonly class CardMandate
{
    public function __construct(private string $reference, private int $amount, private string $currency, private int $startDate, private string $description = '')
    {
    }

    /** @return CardOptions */
    public function toArray(): array
    {
        return ['card' => ['mandate_options' => ['reference' => $this->reference, 'description' => $this->description, 'amount' => $this->amount, 'currency' => $this->currency, 'start_date' => $this->startDate, 'amount_type' => 'maximum', 'interval' => 'sporadic', 'supported_types' => ['india']]]];
    }
}
