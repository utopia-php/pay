<?php

namespace Utopia\Pay\Invoice;

use Utopia\Pay\Credit\Credit;
use Utopia\Pay\Discount\Discount;

final readonly class Invoice
{
    /**
     * Invoice is pending and not yet processed.
     */
    public const STATUS_PENDING = 'pending';

    /**
     * Invoice is due and awaiting payment.
     */
    public const STATUS_DUE = 'due';

    /**
     * Invoice has been refunded.
     */
    public const STATUS_REFUNDED = 'refunded';

    /**
     * Invoice has been cancelled (e.g., below minimum amount).
     */
    public const STATUS_CANCELLED = 'cancelled';

    /**
     * Invoice payment succeeded.
     */
    public const STATUS_SUCCEEDED = 'succeeded';

    /**
     * Invoice payment is being processed.
     */
    public const STATUS_PROCESSING = 'processing';

    /**
     * Invoice payment failed.
     */
    public const STATUS_FAILED = 'failed';


    /** @var list<Discount> */
    private array $discounts;

    /** @var list<Credit> */
    public array $credits;

    /**
     * @param array<Discount|array{id?: string, '$id'?: string, value?: float|null, description?: string, type?: string}> $discounts
     * @param array<Credit|array{id?: string, '$id'?: string, credits?: float, creditsUsed?: float, status?: string}> $credits
     * @param array<mixed> $address
     * @param list<string> $creditsIds
     */
    public function __construct(
        private string $id,
        private float $amount,
        public string $status = self::STATUS_PENDING,
        private string $currency = 'USD',
        array $discounts = [],
        array $credits = [],
        private array $address = [],
        public float $grossAmount = 0,
        public float $taxAmount = 0,
        public float $vatAmount = 0,
        public float $creditsUsed = 0,
        public array $creditsIds = [],
        public float $discountTotal = 0,
    ) {
        $this->discounts = array_values(array_map(static fn ($discount) => $discount instanceof Discount ? $discount : Discount::fromArray($discount), $discounts));
        $this->credits = array_values(array_map(static fn ($credit) => $credit instanceof Credit ? $credit : Credit::fromArray($credit), $credits));
    }

    /** Return a finalized invoice without modifying this invoice or its credits. */
    public function finalize(): self
    {
        $amount = round($this->amount, 2);
        $discounts = $this->discounts;
        $discountTotal = 0.0;
        usort($discounts, static fn (Discount $a, Discount $b) => ($a->type === Discount::TYPE_PERCENTAGE) <=> ($b->type === Discount::TYPE_PERCENTAGE));
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
        $status = $amount == 0 ? self::STATUS_SUCCEEDED : ($amount < 0.50 ? self::STATUS_CANCELLED : self::STATUS_DUE);

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

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'amount' => $this->amount,
            'status' => $this->status,
            'currency' => $this->currency,
            'grossAmount' => $this->grossAmount,
            'taxAmount' => $this->taxAmount,
            'vatAmount' => $this->vatAmount,
            'address' => $this->address,
            'discounts' => array_map(static fn (Discount $discount) => $discount->toArray(), $this->discounts),
            'credits' => array_map(static fn (Credit $credit) => $credit->toArray(), $this->credits),
            'creditsUsed' => $this->creditsUsed,
            'creditsIds' => $this->creditsIds,
            'discountTotal' => $this->discountTotal,
        ];
    }

    /**
     * @param array{id?: string, '$id'?: string, amount?: float, status?: string, currency?: string, grossAmount?: float, taxAmount?: float, vatAmount?: float, address?: array<mixed>, discounts?: array<array{id?: string, '$id'?: string, value?: float|null, description?: string, type?: string}>, credits?: array<array{id?: string, '$id'?: string, credits?: float, creditsUsed?: float, status?: string}>, creditsUsed?: float, creditsIds?: list<string>, discountTotal?: float} $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? $data['$id'] ?? uniqid('invoice_'),
            amount: $data['amount'] ?? 0,
            status: $data['status'] ?? self::STATUS_PENDING,
            currency: $data['currency'] ?? 'USD',
            discounts: $data['discounts'] ?? [],
            credits: $data['credits'] ?? [],
            address: $data['address'] ?? [],
            grossAmount: $data['grossAmount'] ?? 0,
            taxAmount: $data['taxAmount'] ?? 0,
            vatAmount: $data['vatAmount'] ?? 0,
            creditsUsed: $data['creditsUsed'] ?? 0,
            creditsIds: $data['creditsIds'] ?? [],
            discountTotal: $data['discountTotal'] ?? 0,
        );
    }
}
