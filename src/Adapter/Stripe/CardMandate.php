<?php

declare(strict_types=1);

namespace Utopia\Pay\Adapter\Stripe;

use Utopia\Pay\Adapter\Stripe\CardMandate\AmountType;
use Utopia\Pay\Adapter\Stripe\CardMandate\Interval;
use Utopia\Pay\Adapter\Stripe\CardMandate\SupportedType;
use Utopia\Pay\CardMandate as PayCardMandate;
use Utopia\Pay\Exception\InvalidRequest;

/** @phpstan-type CardOptions array{card: array{mandate_options: array{reference: string, description: string, amount: int, currency: string, start_date: int, amount_type: string, interval: string, supported_types: list<string>}}} */
final readonly class CardMandate
{
    public function __construct(private PayCardMandate $payCardMandate)
    {
    }

    /** @return CardOptions */
    public function toArray(): array
    {
        $currency = Currency::tryFrom(strtolower($this->payCardMandate->currency))
            ?? throw new InvalidRequest(message: 'Unsupported Stripe mandate currency', code: 400);

        return ['card' => ['mandate_options' => [
            'reference' => $this->payCardMandate->reference,
            'description' => $this->payCardMandate->description,
            'amount' => $this->payCardMandate->amount,
            'currency' => $currency->value,
            'start_date' => $this->payCardMandate->startDate,
            'amount_type' => AmountType::Maximum->value,
            'interval' => Interval::Sporadic->value,
            'supported_types' => [SupportedType::India->value],
        ]]];
    }
}
