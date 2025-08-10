<?php

namespace Utopia\Pay\Invoice;

use Utopia\Pay\Credit\Credit;
use Utopia\Pay\Discount\Discount;

class Invoice
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_DUE = 'due';

    public const STATUS_REFUNDED = 'refunded';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_SUCCEEDED = 'succeeded';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_FAILED = 'failed';

    /**
     * @param  string  $id
     * @param  float  $amount
     * @param  string  $status
     * @param  string  $currency
     * @param  Discount[]  $discounts
     * @param  Credit[]  $credits
     * @param  array  $address
     * @param  float  $grossAmount
     * @param  float  $taxAmount
     * @param  float  $vatAmount
     * @param  float  $creditsUsed
     * @param  string[]  $creditsIds
     */
    public function __construct(
        private string $id,
        private float $amount,
        private string $status = self::STATUS_PENDING,
        private string $currency = 'USD',
        private array $discounts = [],
        private array $credits = [],
        private array $address = [],
        private float $grossAmount = 0,
        private float $taxAmount = 0,
        private float $vatAmount = 0,
        private float $creditsUsed = 0,
        private array $creditsIds = [],
    ) {
        // Properties are already set by promotion, just ensure discounts/credits are objects
        $this->setDiscounts($discounts);
        $this->setCredits($credits);
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * Mark invoice as paid (alias for markAsSucceeded).
     */
    public function markAsPaid(): static
    {
        return $this->markAsSucceeded();
    }

    public function getGrossAmount(): float
    {
        return $this->grossAmount;
    }

    public function setGrossAmount(float $grossAmount): static
    {
        $this->grossAmount = $grossAmount;

        return $this;
    }

    public function getTaxAmount(): float
    {
        return $this->taxAmount;
    }

    public function setTaxAmount(float $taxAmount): static
    {
        $this->taxAmount = $taxAmount;

        return $this;
    }

    public function getVatAmount(): float
    {
        return $this->vatAmount;
    }

    public function setVatAmount(float $vatAmount): static
    {
        $this->vatAmount = $vatAmount;

        return $this;
    }

    public function getAddress(): array
    {
        return $this->address;
    }

    public function setAddress(array $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getDiscounts(): array
    {
        return $this->discounts;
    }

    public function setDiscounts(array $discounts): static
    {
        // Handle both arrays of Discount objects and arrays of arrays
        if (is_array($discounts)) {
            $discountObjects = [];
            foreach ($discounts as $discount) {
                if ($discount instanceof Discount) {
                    $discountObjects[] = $discount;
                } elseif (is_array($discount)) {
                    // Convert array to Discount object for backward compatibility
                    $discountObjects[] = new Discount(
                        $discount['id'] ?? uniqid('discount_'),
                        $discount['value'] ?? 0,
                        $discount['amount'] ?? 0,
                        $discount['description'] ?? '',
                        $discount['type'] ?? Discount::TYPE_FIXED
                    );
                } else {
                    throw new \InvalidArgumentException('Discount must be either a Discount object or an array');
                }
            }
            $this->discounts = $discountObjects;
        } else {
            throw new \InvalidArgumentException('Discounts must be an array');
        }

        return $this;
    }

    public function addDiscount(Discount $discount): static
    {
        $this->discounts[] = $discount;

        return $this;
    }

    public function getCreditsUsed(): float
    {
        return $this->creditsUsed;
    }

    public function setCreditsUsed(float $creditsUsed): static
    {
        $this->creditsUsed = $creditsUsed;

        return $this;
    }

    public function getCreditInternalIds(): array
    {
        return $this->creditsIds;
    }

    public function setCreditInternalIds(array $creditsIds): static
    {
        $this->creditsIds = $creditsIds;

        return $this;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function markAsDue(): static
    {
        $this->status = self::STATUS_DUE;

        return $this;
    }

    /**
     * Mark invoice as succeeded.
     */
    public function markAsSucceeded(): static
    {
        $this->status = self::STATUS_SUCCEEDED;
        return $this;
    }

    public function markAsCancelled(): static
    {
        $this->status = self::STATUS_CANCELLED;

        return $this;
    }

    public function isNegativeAmount(): bool
    {
        return $this->amount < 0;
    }

    public function isBelowMinimumAmount($minimumAmount = 0.50)
    {
        return $this->grossAmount < $minimumAmount;
    }

    public function isZeroAmount(): bool
    {
        return $this->grossAmount == 0;
    }

    public function getDiscountTotal(): float
    {
        $total = 0;
        foreach ($this->discounts as $discount) {
            $total += $discount->getAmount();
        }

        return $total;
    }

    public function getDiscountsAsArray(): array
    {
        $discountArray = [];
        foreach ($this->discounts as $discount) {
            $discountArray[] = $discount->toArray();
        }

        return $discountArray;
    }

    public function getCredits(): array
    {
        return $this->credits;
    }

    public function setCredits(array $credits): static
    {
        // Validate that all items are Credit objects
        $creditObjects = [];
        foreach ($credits as $credit) {
            if ($credit instanceof Credit) {
                $creditObjects[] = $credit;
            } elseif (is_array($credit)) {
                $creditObjects[] = Credit::fromArray($credit);
            } else {
                throw new \InvalidArgumentException('All items in credits array must be Credit objects or arrays with id and credits keys');
            }
        }
        $this->credits = $creditObjects;

        return $this;
    }

    public function addCredit(Credit $credit): static
    {
        $this->credits[] = $credit;

        return $this;
    }

    public function getTotalAvailableCredits(): float
    {
        $total = 0;
        foreach ($this->credits as $credit) {
            $total += $credit->getCredits();
        }

        return $total;
    }

    public function applyCredits(): static
    {
        $amount = $this->grossAmount;
        $totalCreditsUsed = 0;
        $creditsIds = [];

        foreach ($this->credits as $credit) {
            if ($amount == 0) {
                break;
            }

            $creditToUse = $credit->useCredits($amount);
            $amount = $amount - $creditToUse;
            $totalCreditsUsed += $creditToUse;
            $creditsIds[] = $credit->getId();
            if ($this->isZeroAmount()) {
                continue;
            }
        }

        $amount = round($amount, 2);
        $this->setGrossAmount($amount);
        $this->setCreditsUsed($totalCreditsUsed);
        $this->setCreditInternalIds($creditsIds);

        return $this;
    }

    public function applyDiscounts(): static
    {
        $discounts = $this->discounts;
        $amount = $this->grossAmount;

        foreach ($discounts as $discount) {
            if ($amount == 0) {
                break;
            }
            $discountToUse = $discount->calculateDiscount($amount);

            if ($discountToUse <= 0) {
                continue;
            }
            $amount -= $discountToUse;
        }

        $amount = round($amount, 2);
        $this->setGrossAmount($amount);

        return $this;
    }

    public function finalize(): static
    {
        // Set the initial gross amount and round to 2 decimal places
        $this->grossAmount = round($this->amount, 2);

        // Apply discounts first
        $this->applyDiscounts();

        // Round tax and VAT amounts before adding
        $this->taxAmount = round($this->taxAmount, 2);
        $this->vatAmount = round($this->vatAmount, 2);

        // Add rounded tax and VAT to the gross amount
        $this->grossAmount = $this->grossAmount + $this->taxAmount + $this->vatAmount;

        // Then apply credits
        $this->applyCredits();

        // Update status based on final amount
        if ($this->isZeroAmount()) {
            $this->markAsSucceeded();
        } elseif ($this->isBelowMinimumAmount()) {
            $this->markAsCancelled();
        } else {
            $this->markAsDue();
        }

        return $this;
    }

    public function hasDiscounts(): bool
    {
        return ! empty($this->discounts);
    }

    public function hasCredits(): bool
    {
        return ! empty($this->credits);
    }

    public function getCreditsAsArray(): array
    {
        $creditsArray = [];
        foreach ($this->credits as $credit) {
            $creditsArray[] = $credit->toArray();
        }

        return $creditsArray;
    }

    public function findDiscountById(string $id): ?Discount
    {
        foreach ($this->discounts as $discount) {
            if ($discount->getId() === $id) {
                return $discount;
            }
        }

        return null;
    }

    public function findCreditById(string $id): ?Credit
    {
        foreach ($this->credits as $credit) {
            if ($credit->getId() === $id) {
                return $credit;
            }
        }

        return null;
    }

    public function removeDiscountById(string $id): static
    {
        $this->discounts = array_filter($this->discounts, function ($discount) use ($id) {
            return $discount->getId() !== $id;
        });

        return $this;
    }

    public function removeCreditById(string $id): static
    {
        $this->credits = array_filter($this->credits, function ($credit) use ($id) {
            return $credit->getId() !== $id;
        });

        return $this;
    }

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
            'discounts' => $this->getDiscountsAsArray(),
            'credits' => $this->getCreditsAsArray(),
            'creditsUsed' => $this->creditsUsed,
            'creditsIds' => $this->creditsIds,
        ];
    }

    public static function fromArray(array $data): self
    {
        $id = $data['id'] ?? $data['$id'] ?? uniqid('invoice_');
        $amount = $data['amount'] ?? 0;
        $status = $data['status'] ?? self::STATUS_PENDING;
        $currency = $data['currency'] ?? 'USD';
        $grossAmount = $data['grossAmount'] ?? 0;
        $taxAmount = $data['taxAmount'] ?? 0;
        $vatAmount = $data['vatAmount'] ?? 0;
        $address = $data['address'] ?? [];
        $discounts = isset($data['discounts']) ? array_map(fn ($d) => Discount::fromArray($d), $data['discounts']) : [];
        $credits = isset($data['credits']) ? array_map(fn ($c) => Credit::fromArray($c), $data['credits']) : [];
        $creditsUsed = $data['creditsUsed'] ?? 0;
        $creditsIds = $data['creditsIds'] ?? [];

        return new self(
            id: $id,
            amount: $amount,
            status: $status,
            currency: $currency,
            discounts: $discounts,
            credits: $credits,
            address: $address,
            grossAmount: $grossAmount,
            taxAmount: $taxAmount,
            vatAmount: $vatAmount,
            creditsUsed: $creditsUsed,
            creditsIds: $creditsIds
        );
    }
}
