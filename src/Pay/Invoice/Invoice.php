<?php

namespace Utopia\Pay\Invoice;

use Utopia\Pay\Credit\Credit;
use Utopia\Pay\Discount\Discount;

/**
 * Invoice class for managing payment invoices.
 *
 * This class handles invoice creation, status management, discount and credit application,
 * and invoice finalization with tax calculations.
 */
class Invoice
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

    /**
     * Create a new Invoice instance.
     *
     * @param  string  $id  Unique identifier for the invoice
     * @param  float  $amount  Base amount before discounts, taxes, and credits
     * @param  string  $status  Invoice status (use STATUS_* constants)
     * @param  string  $currency  Currency code (default: 'USD')
     * @param  Discount[]  $discounts  Array of Discount objects to apply
     * @param  Credit[]  $credits  Array of Credit objects available for this invoice
     * @param  array  $address  Billing address information
     * @param  float  $grossAmount  Final amount after discounts, taxes, and credits
     * @param  float  $taxAmount  Tax amount to add
     * @param  float  $vatAmount  VAT amount to add
     * @param  float  $creditsUsed  Total credits applied to this invoice
     * @param  string[]  $creditsIds  IDs of credits that were applied
     * @param  float  $discountTotal  Total discount amount applied to this invoice
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
        private float $discountTotal = 0,
    ) {
        $this->setDiscounts($discounts);
        $this->setCredits($credits);
    }

    /**
     * Get the invoice ID.
     *
     * @return string The unique invoice identifier
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Get the base invoice amount (before discounts, taxes, and credits).
     *
     * @return float The base amount
     */
    public function getAmount(): float
    {
        return $this->amount;
    }

    /**
     * Get the invoice currency code.
     *
     * @return string The currency code (e.g., 'USD', 'EUR')
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * Get the current invoice status.
     *
     * @return string The status (one of STATUS_* constants)
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * Mark invoice as paid (alias for markAsSucceeded).
     *
     * @return static
     */
    public function markAsPaid(): static
    {
        return $this->markAsSucceeded();
    }

    /**
     * Get the gross amount (final amount after all calculations).
     *
     * @return float The gross amount
     */
    public function getGrossAmount(): float
    {
        return $this->grossAmount;
    }

    /**
     * Set the gross amount.
     *
     * @param  float  $grossAmount  The gross amount to set
     * @return static
     */
    public function setGrossAmount(float $grossAmount): static
    {
        $this->grossAmount = $grossAmount;

        return $this;
    }

    /**
     * Get the tax amount.
     *
     * @return float The tax amount
     */
    public function getTaxAmount(): float
    {
        return $this->taxAmount;
    }

    /**
     * Set the tax amount to add to the invoice.
     *
     * @param  float  $taxAmount  The tax amount
     * @return static
     */
    public function setTaxAmount(float $taxAmount): static
    {
        $this->taxAmount = $taxAmount;

        return $this;
    }

    /**
     * Get the VAT amount.
     *
     * @return float The VAT amount
     */
    public function getVatAmount(): float
    {
        return $this->vatAmount;
    }

    /**
     * Set the VAT amount to add to the invoice.
     *
     * @param  float  $vatAmount  The VAT amount
     * @return static
     */
    public function setVatAmount(float $vatAmount): static
    {
        $this->vatAmount = $vatAmount;

        return $this;
    }

    /**
     * Get the billing address.
     *
     * @return array The address array
     */
    public function getAddress(): array
    {
        return $this->address;
    }

    /**
     * Set the billing address.
     *
     * @param  array  $address  The address information
     * @return static
     */
    public function setAddress(array $address): static
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get all discounts attached to this invoice.
     *
     * @return Discount[] Array of Discount objects
     */
    public function getDiscounts(): array
    {
        return $this->discounts;
    }

    /**
     * Set the discounts for this invoice.
     *
     * Accepts either Discount objects or arrays that will be converted to Discount objects.
     *
     * @param  array  $discounts  Array of Discount objects or arrays
     * @return static
     *
     * @throws \InvalidArgumentException If invalid discount format is provided
     */
    public function setDiscounts(array $discounts): static
    {
        // Handle both arrays of Discount objects and arrays of arrays
        if (is_array($discounts)) {
            $discountObjects = [];
            foreach ($discounts as $discount) {
                if ($discount instanceof Discount) {
                    $discountObjects[] = $discount;
                } elseif (is_array($discount)) {
                    // Convert array to Discount object using fromArray for backward compatibility
                    $discountObjects[] = Discount::fromArray($discount);
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

    /**
     * Add a discount to the invoice.
     *
     * @param  Discount  $discount  The discount to add
     * @return static
     */
    public function addDiscount(Discount $discount): static
    {
        $this->discounts[] = $discount;

        return $this;
    }

    /**
     * Get the total amount of credits used on this invoice.
     *
     * @return float The total credits used
     */
    public function getCreditsUsed(): float
    {
        return $this->creditsUsed;
    }

    /**
     * Set the total amount of credits used.
     *
     * @param  float  $creditsUsed  The credits used amount
     * @return static
     */
    public function setCreditsUsed(float $creditsUsed): static
    {
        $this->creditsUsed = $creditsUsed;

        return $this;
    }

    /**
     * Get the IDs of credits that were applied to this invoice.
     *
     * @return string[] Array of credit IDs
     */
    public function getCreditInternalIds(): array
    {
        return $this->creditsIds;
    }

    /**
     * Set the IDs of credits that were applied.
     *
     * @param  string[]  $creditsIds  Array of credit IDs
     * @return static
     */
    public function setCreditInternalIds(array $creditsIds): static
    {
        $this->creditsIds = $creditsIds;

        return $this;
    }

    /**
     * Set the invoice status.
     *
     * @param  string  $status  The status to set (use STATUS_* constants)
     * @return static
     */
    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Mark the invoice as due.
     *
     * @return static
     */
    public function markAsDue(): static
    {
        $this->status = self::STATUS_DUE;

        return $this;
    }

    /**
     * Mark invoice as succeeded.
     *
     * @return static
     */
    public function markAsSucceeded(): static
    {
        $this->status = self::STATUS_SUCCEEDED;

        return $this;
    }

    /**
     * Mark the invoice as cancelled.
     *
     * @return static
     */
    public function markAsCancelled(): static
    {
        $this->status = self::STATUS_CANCELLED;

        return $this;
    }

    /**
     * Check if the invoice amount is negative.
     *
     * @return bool True if amount is negative
     */
    public function isNegativeAmount(): bool
    {
        return $this->amount < 0;
    }

    /**
     * Check if the gross amount is below the minimum threshold.
     *
     * @param  float  $minimumAmount  The minimum amount threshold (default: 0.50)
     * @return bool True if below minimum
     */
    public function isBelowMinimumAmount($minimumAmount = 0.50)
    {
        return $this->grossAmount < $minimumAmount;
    }

    /**
     * Check if the gross amount is zero.
     *
     * @return bool True if amount is zero
     */
    public function isZeroAmount(): bool
    {
        return $this->grossAmount == 0;
    }

    /**
     * Get the total discount amount that was applied.
     *
     * Returns 0 if discounts haven't been applied yet.
     * After applyDiscounts() is called, returns the actual discount amount applied.
     *
     * @return float The total discount amount applied
     */
    public function getDiscountTotal(): float
    {
        return $this->discountTotal;
    }

    /**
     * Set the total discount amount applied.
     *
     * @param  float  $discountTotal  The total discount amount
     * @return static
     */
    public function setDiscountTotal(float $discountTotal): static
    {
        $this->discountTotal = $discountTotal;

        return $this;
    }

    /**
     * Get discounts as array representation.
     *
     * @return array Array of discount data
     */
    public function getDiscountsAsArray(): array
    {
        $discountArray = [];
        foreach ($this->discounts as $discount) {
            $discountArray[] = $discount->toArray();
        }

        return $discountArray;
    }

    /**
     * Get all credits attached to this invoice.
     *
     * @return Credit[] Array of Credit objects
     */
    public function getCredits(): array
    {
        return $this->credits;
    }

    /**
     * Set the credits for this invoice.
     *
     * Accepts either Credit objects or arrays that will be converted to Credit objects.
     *
     * @param  array  $credits  Array of Credit objects or arrays
     * @return static
     *
     * @throws \InvalidArgumentException If invalid credit format is provided
     */
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

    /**
     * Add a credit to the invoice.
     *
     * @param  Credit  $credit  The credit to add
     * @return static
     */
    public function addCredit(Credit $credit): static
    {
        $this->credits[] = $credit;

        return $this;
    }

    /**
     * Get the total available credits from all credit objects.
     *
     * @return float The total available credits
     */
    public function getTotalAvailableCredits(): float
    {
        $total = 0;
        foreach ($this->credits as $credit) {
            $total += $credit->getCredits();
        }

        return $total;
    }

    /**
     * Apply available credits to the invoice amount.
     *
     * Credits are applied in order until the amount reaches zero or all credits are used.
     * Updates the gross amount and tracks which credits were used.
     *
     * @return static
     */
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
        }

        $amount = round($amount, 2);
        $this->setGrossAmount($amount);
        $this->setCreditsUsed($totalCreditsUsed);
        $this->setCreditInternalIds($creditsIds);

        return $this;
    }

    /**
     * Apply all discounts to the invoice amount.
     *
     * Discounts are applied in the correct order:
     * 1. Fixed amount discounts first
     * 2. Percentage discounts second (applied to amount after fixed discounts)
     *
     * Updates the gross amount and tracks total discount applied.
     *
     * @return static
     */
    public function applyDiscounts(): static
    {
        $discounts = $this->discounts;
        $amount = $this->grossAmount;
        $totalDiscount = 0;

        // Sort discounts: fixed first, then percentage
        usort($discounts, function ($a, $b) {
            if ($a->getType() === Discount::TYPE_FIXED && $b->getType() === Discount::TYPE_PERCENTAGE) {
                return -1;
            }
            if ($a->getType() === Discount::TYPE_PERCENTAGE && $b->getType() === Discount::TYPE_FIXED) {
                return 1;
            }

            return 0;
        });

        foreach ($discounts as $discount) {
            if ($amount <= 0) {
                break;
            }
            $discountToUse = $discount->calculateDiscount($amount);

            if ($discountToUse <= 0) {
                continue;
            }
            $amount -= $discountToUse;
            $totalDiscount += $discountToUse;
        }

        $amount = round($amount, 2);
        $totalDiscount = round($totalDiscount, 2);

        $this->setGrossAmount($amount);
        $this->setDiscountTotal($totalDiscount);

        return $this;
    }

    /**
     * Finalize the invoice by applying all discounts, taxes, and credits.
     *
     * Process order:
     * 1. Apply discounts to the base amount
     * 2. Add tax and VAT amounts
     * 3. Apply available credits
     * 4. Update invoice status based on final amount
     *
     * @return static
     */
    public function finalize(): static
    {
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

    /**
     * Check if the invoice has any discounts.
     *
     * @return bool True if discounts exist
     */
    public function hasDiscounts(): bool
    {
        return ! empty($this->discounts);
    }

    /**
     * Check if the invoice has any credits.
     *
     * @return bool True if credits exist
     */
    public function hasCredits(): bool
    {
        return ! empty($this->credits);
    }

    /**
     * Get credits as array representation.
     *
     * @return array Array of credit data
     */
    public function getCreditsAsArray(): array
    {
        $creditsArray = [];
        foreach ($this->credits as $credit) {
            $creditsArray[] = $credit->toArray();
        }

        return $creditsArray;
    }

    /**
     * Find a discount by its ID.
     *
     * @param  string  $id  The discount ID to search for
     * @return Discount|null The discount object or null if not found
     */
    public function findDiscountById(string $id): ?Discount
    {
        foreach ($this->discounts as $discount) {
            if ($discount->getId() === $id) {
                return $discount;
            }
        }

        return null;
    }

    /**
     * Find a credit by its ID.
     *
     * @param  string  $id  The credit ID to search for
     * @return Credit|null The credit object or null if not found
     */
    public function findCreditById(string $id): ?Credit
    {
        foreach ($this->credits as $credit) {
            if ($credit->getId() === $id) {
                return $credit;
            }
        }

        return null;
    }

    /**
     * Remove a discount from the invoice by its ID.
     *
     * @param  string  $id  The discount ID to remove
     * @return static
     */
    public function removeDiscountById(string $id): static
    {
        $this->discounts = array_filter($this->discounts, function ($discount) use ($id) {
            return $discount->getId() !== $id;
        });

        return $this;
    }

    /**
     * Remove a credit from the invoice by its ID.
     *
     * @param  string  $id  The credit ID to remove
     * @return static
     */
    public function removeCreditById(string $id): static
    {
        $this->credits = array_filter($this->credits, function ($credit) use ($id) {
            return $credit->getId() !== $id;
        });

        return $this;
    }

    /**
     * Convert the invoice to an array representation.
     *
     * @return array The invoice data as an array
     */
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
            'discountTotal' => $this->discountTotal,
        ];
    }

    /**
     * Create an Invoice instance from an array.
     *
     * @param  array  $data  The invoice data array
     * @return self The created Invoice instance
     */
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
        $discountTotal = $data['discountTotal'] ?? 0;

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
            creditsIds: $creditsIds,
            discountTotal: $discountTotal
        );
    }
}
