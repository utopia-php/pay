<?php

declare(strict_types=1);

namespace Utopia\Pay\Invoice;

use Utopia\Pay\Address;
use Utopia\Pay\Credit\Credit;
use Utopia\Pay\Discount\Discount;
use Utopia\Pay\Discount\Type as DiscountType;
use Utopia\Pay\Payload;

final readonly class Invoice
{
    /**
     * @param list<Discount> $discounts
     * @param list<Credit> $credits
     * @param list<string> $creditsIds
     */
    public function __construct(
        private string $id,
        private float $amount,
        public Status $status = Status::Pending,
        private string $currency = 'USD',
        private array $discounts = [],
        public array $credits = [],
        private ?Address $address = null,
        public float $grossAmount = 0,
        public float $taxAmount = 0,
        public float $vatAmount = 0,
        public float $creditsUsed = 0,
        public array $creditsIds = [],
        public float $discountTotal = 0,
    ) {
    }

    /** Return a finalized invoice without modifying this invoice or its credits. */
    public function finalize(): self
    {
        $amount = round($this->amount, 2);
        $discounts = $this->discounts;
        $discountTotal = 0.0;
        usort($discounts, static fn (Discount $a, Discount $b) => ($a->type === DiscountType::Percentage) <=> ($b->type === DiscountType::Percentage));
        foreach ($discounts as $discount) {
            if ($amount <= 0) {
                break;
            }
            $used = $discount->calculateDiscount($amount);
            if ($used > 0) {
                $amount -= $used;
                $discountTotal += $used;
            }
        }

        $taxAmount = round($this->taxAmount, 2);
        $vatAmount = round($this->vatAmount, 2);
        $amount = round($amount, 2) + $taxAmount + $vatAmount;
        $credits = $this->credits;
        $creditsUsed = 0.0;
        $creditsIds = [];
        foreach ($credits as $index => $credit) {
            if ($amount <= 0) {
                break;
            }
            $updated = $credit->useCredits($amount);
            $used = $updated->creditsUsed - $credit->creditsUsed;
            $credits[$index] = $updated;
            if ($used > 0) {
                $amount -= $used;
                $creditsUsed += $used;
                $creditsIds[] = $credit->id;
            }
        }
        $amount = round($amount, 2);
        $status = $amount == 0 ? Status::Succeeded : ($amount < 0.50 ? Status::Cancelled : Status::Due);

        return new self(
            id: $this->id,
            amount: $this->amount,
            status: $status,
            currency: $this->currency,
            discounts: $this->discounts,
            credits: $credits,
            address: $this->address,
            grossAmount: $amount,
            taxAmount: $taxAmount,
            vatAmount: $vatAmount,
            creditsUsed: $creditsUsed,
            creditsIds: $creditsIds,
            discountTotal: round($discountTotal, 2),
        );
    }

    public function isNegativeAmount(): bool
    {
        return $this->amount < 0;
    }

    public function isBelowMinimumAmount(float $minimumAmount = 0.50): bool
    {
        return $this->grossAmount < $minimumAmount;
    }

    public function isZeroAmount(): bool
    {
        return $this->grossAmount == 0;
    }

    /** @return array{id: string, amount: float, status: string, currency: string, grossAmount: float, taxAmount: float, vatAmount: float, address: array{city?: string, country?: string, line1?: string|null, line2?: string|null, postal_code?: string|null, state?: string|null}, discounts: list<array{id: string, value: float, description: string, type: string}>, credits: list<array{id: string, credits: float, creditsUsed: float, status: string}>, creditsUsed: float, creditsIds: list<string>, discountTotal: float} */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'amount' => $this->amount,
            'status' => $this->status->value,
            'currency' => $this->currency,
            'grossAmount' => $this->grossAmount,
            'taxAmount' => $this->taxAmount,
            'vatAmount' => $this->vatAmount,
            'address' => $this->address?->asArray() ?? [],
            'discounts' => array_map(static fn (Discount $discount) => $discount->toArray(), $this->discounts),
            'credits' => array_map(static fn (Credit $credit) => $credit->toArray(), $this->credits),
            'creditsUsed' => $this->creditsUsed,
            'creditsIds' => $this->creditsIds,
            'discountTotal' => $this->discountTotal,
        ];
    }

    /**
     * @param array{id?: string, '$id'?: string, amount?: float, status?: string, currency?: string, grossAmount?: float, taxAmount?: float, vatAmount?: float, address?: array{city?: string, country?: string, line1?: string|null, line2?: string|null, postal_code?: string|null, state?: string|null}, discounts?: array<array{id?: string, '$id'?: string, value?: float|null, description?: string, type?: string}>, credits?: array<array{id?: string, '$id'?: string, credits?: float, creditsUsed?: float, status?: string}>, creditsUsed?: float, creditsIds?: list<string>, discountTotal?: float} $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? $data['$id'] ?? uniqid('invoice_'),
            amount: $data['amount'] ?? 0,
            status: isset($data['status']) ? Status::from($data['status']) : Status::Pending,
            currency: $data['currency'] ?? 'USD',
            discounts: array_values(array_map(Discount::fromArray(...), $data['discounts'] ?? [])),
            credits: array_values(array_map(Credit::fromArray(...), $data['credits'] ?? [])),
            address: !empty($data['address']) ? Address::fromPayload(new Payload((object) $data['address'])) : null,
            grossAmount: $data['grossAmount'] ?? 0,
            taxAmount: $data['taxAmount'] ?? 0,
            vatAmount: $data['vatAmount'] ?? 0,
            creditsUsed: $data['creditsUsed'] ?? 0,
            creditsIds: $data['creditsIds'] ?? [],
            discountTotal: $data['discountTotal'] ?? 0,
        );
    }
}
