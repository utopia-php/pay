<?php

namespace Utopia\Pay\Invoice;

use Utopia\Pay\Credit\Credit;
use Utopia\Pay\Discount\Discount;

/**
 * Invoice class for managing payment invoices.
 *
 * This class handles invoice creation, status management, discount and credit application,
 * and invoice finalization with tax calculations. Designed to be extended for application-specific
 * invoice implementations.
 */
class Invoice
{
    // ==================== STATUS CONSTANTS ====================

    /**
     * Invoice is in draft state, amounts can still be adjusted.
     */
    public const STATUS_DRAFT = 'draft';

    /**
     * Invoice is due and awaiting payment.
     */
    public const STATUS_DUE = 'due';

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
     * Invoice has been refunded.
     */
    public const STATUS_REFUNDED = 'refunded';

    /**
     * Invoice has been cancelled (e.g., below minimum amount).
     */
    public const STATUS_CANCELLED = 'cancelled';

    /**
     * Invoice requires 3D Secure or other authentication.
     */
    public const STATUS_REQUIRES_AUTH = 'requires_authentication';

    /**
     * Invoice was abandoned (e.g., associated entity not found).
     */
    public const STATUS_ABANDONED = 'abandoned';

    /**
     * Invoice payment is disputed.
     */
    public const STATUS_DISPUTED = 'disputed';

    // ==================== INVOICE TYPES ====================

    /**
     * Recurring subscription invoice.
     */
    public const TYPE_SUBSCRIPTION = 'subscription';

    /**
     * One-time payment invoice.
     */
    public const TYPE_ONE_TIME = 'one_time';

    /**
     * Custom invoice type.
     */
    public const TYPE_CUSTOM = 'custom';

    // ==================== STATE MACHINE ====================

    /**
     * Valid state transitions.
     * Key: current state, Value: array of allowed next states.
     * Can be overridden by extending classes to add more transitions.
     */
    protected const TRANSITIONS = [
        self::STATUS_DRAFT => [self::STATUS_DUE, self::STATUS_SUCCEEDED, self::STATUS_CANCELLED],
        self::STATUS_DUE => [self::STATUS_PROCESSING, self::STATUS_SUCCEEDED, self::STATUS_CANCELLED, self::STATUS_FAILED],
        self::STATUS_PROCESSING => [self::STATUS_SUCCEEDED, self::STATUS_FAILED, self::STATUS_REQUIRES_AUTH],
        self::STATUS_FAILED => [self::STATUS_DUE, self::STATUS_PROCESSING, self::STATUS_ABANDONED, self::STATUS_CANCELLED],
        self::STATUS_REQUIRES_AUTH => [self::STATUS_SUCCEEDED, self::STATUS_FAILED],
        self::STATUS_SUCCEEDED => [self::STATUS_DISPUTED, self::STATUS_REFUNDED],
        self::STATUS_DISPUTED => [],
        self::STATUS_CANCELLED => [],
        self::STATUS_ABANDONED => [],
        self::STATUS_REFUNDED => [],
    ];

    // ==================== PROPERTIES ====================

    // Core invoice properties (protected for inheritance)
    protected string $id;
    protected float $amount;
    protected string $status = self::STATUS_DRAFT;
    protected string $currency = 'USD';
    protected array $discounts = [];
    protected array $credits = [];
    protected array $address = [];
    protected float $grossAmount = 0;
    protected float $taxAmount = 0;
    protected float $vatAmount = 0;
    protected float $creditsUsed = 0;
    protected array $creditsIds = [];
    protected float $discountTotal = 0;

    // Invoice type and period
    protected string $type = self::TYPE_SUBSCRIPTION;
    protected ?string $from = null;
    protected ?string $to = null;
    protected ?string $dueAt = null;
    protected ?string $issuedAt = null;
    protected ?string $paidAt = null;

    // Payment tracking
    protected ?string $paymentId = null;
    protected ?string $clientSecret = null;
    protected ?string $lastError = null;
    protected int $attempts = 0;
    protected ?string $nextAttemptAt = null;

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
        string $id,
        float $amount,
        string $status = self::STATUS_DRAFT,
        string $currency = 'USD',
        array $discounts = [],
        array $credits = [],
        array $address = [],
        float $grossAmount = 0,
        float $taxAmount = 0,
        float $vatAmount = 0,
        float $creditsUsed = 0,
        array $creditsIds = [],
        float $discountTotal = 0,
    ) {
        $this->id = $id;
        $this->amount = $amount;
        $this->status = $status;
        $this->currency = $currency;
        $this->address = $address;
        $this->grossAmount = $grossAmount;
        $this->taxAmount = $taxAmount;
        $this->vatAmount = $vatAmount;
        $this->creditsUsed = $creditsUsed;
        $this->creditsIds = $creditsIds;
        $this->discountTotal = $discountTotal;

        $this->setDiscounts($discounts);
        $this->setCredits($credits);
    }

    // ==================== CORE GETTERS ====================

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
     * Get the gross amount (final amount after all calculations).
     *
     * @return float The gross amount
     */
    public function getGrossAmount(): float
    {
        return $this->grossAmount;
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
     * Get the VAT amount.
     *
     * @return float The VAT amount
     */
    public function getVatAmount(): float
    {
        return $this->vatAmount;
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
     * Get all discounts attached to this invoice.
     *
     * @return Discount[] Array of Discount objects
     */
    public function getDiscounts(): array
    {
        return $this->discounts;
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
     * Get the IDs of credits that were applied to this invoice.
     *
     * @return string[] Array of credit IDs
     */
    public function getCreditInternalIds(): array
    {
        return $this->creditsIds;
    }

    /**
     * Get the total discount amount that was applied.
     *
     * @return float The total discount amount applied
     */
    public function getDiscountTotal(): float
    {
        return $this->discountTotal;
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

    // ==================== TYPE AND PERIOD ====================

    /**
     * Get the invoice type.
     *
     * @return string The invoice type (one of TYPE_* constants)
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Set the invoice type.
     *
     * @param  string  $type  The invoice type
     * @return static
     */
    public function setType(string $type): static
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Get the period start date.
     *
     * @return string|null The period start date
     */
    public function getFrom(): ?string
    {
        return $this->from;
    }

    /**
     * Get the period end date.
     *
     * @return string|null The period end date
     */
    public function getTo(): ?string
    {
        return $this->to;
    }

    /**
     * Get the due date.
     *
     * @return string|null The due date
     */
    public function getDueAt(): ?string
    {
        return $this->dueAt;
    }

    /**
     * Set the invoice period.
     *
     * @param  string  $from  Period start date
     * @param  string  $to  Period end date
     * @return static
     */
    public function setPeriod(string $from, string $to): static
    {
        $this->from = $from;
        $this->to = $to;
        return $this;
    }

    /**
     * Set the due date.
     *
     * @param  string  $dueAt  The due date
     * @return static
     */
    public function setDueAt(string $dueAt): static
    {
        $this->dueAt = $dueAt;
        return $this;
    }

    /**
     * Get the issued date.
     *
     * @return string|null The issued date
     */
    public function getIssuedAt(): ?string
    {
        return $this->issuedAt;
    }

    /**
     * Set the issued date.
     *
     * @param  string|null  $issuedAt  The issued date
     * @return static
     */
    protected function setIssuedAt(?string $issuedAt): static
    {
        $this->issuedAt = $issuedAt;
        return $this;
    }

    /**
     * Get the paid date.
     *
     * @return string|null The paid date
     */
    public function getPaidAt(): ?string
    {
        return $this->paidAt;
    }

    /**
     * Set the paid date.
     *
     * @param  string|null  $paidAt  The paid date
     * @return static
     */
    protected function setPaidAt(?string $paidAt): static
    {
        $this->paidAt = $paidAt;
        return $this;
    }

    // ==================== PAYMENT TRACKING ====================

    /**
     * Get the payment ID.
     *
     * @return string|null The payment ID
     */
    public function getPaymentId(): ?string
    {
        return $this->paymentId;
    }

    /**
     * Set the payment ID.
     *
     * @param  string|null  $paymentId  The payment ID
     * @return static
     */
    public function setPaymentId(?string $paymentId): static
    {
        $this->paymentId = $paymentId;
        return $this;
    }

    /**
     * Get the client secret for authentication.
     *
     * @return string|null The client secret
     */
    public function getClientSecret(): ?string
    {
        return $this->clientSecret;
    }

    /**
     * Set the client secret for authentication.
     *
     * @param  string|null  $clientSecret  The client secret
     * @return static
     */
    public function setClientSecret(?string $clientSecret): static
    {
        $this->clientSecret = $clientSecret;
        return $this;
    }

    /**
     * Get the last error message.
     *
     * @return string|null The last error message
     */
    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    /**
     * Set the last error message.
     *
     * @param  string|null  $lastError  The error message
     * @return static
     */
    public function setLastError(?string $lastError): static
    {
        $this->lastError = $lastError;
        return $this;
    }

    /**
     * Get the number of payment attempts.
     *
     * @return int The number of attempts
     */
    public function getAttempts(): int
    {
        return $this->attempts;
    }

    /**
     * Set the number of payment attempts.
     *
     * @param  int  $attempts  The number of attempts
     * @return static
     */
    public function setAttempts(int $attempts): static
    {
        $this->attempts = $attempts;
        return $this;
    }

    /**
     * Get the next retry attempt date.
     *
     * @return string|null The next attempt date
     */
    public function getNextAttemptAt(): ?string
    {
        return $this->nextAttemptAt;
    }

    /**
     * Set the next retry attempt date.
     *
     * @param  string|null  $nextAttemptAt  The next attempt date
     * @return static
     */
    public function setNextAttemptAt(?string $nextAttemptAt): static
    {
        $this->nextAttemptAt = $nextAttemptAt;
        return $this;
    }

    // ==================== CORE SETTERS ====================

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
     * Set the discounts for this invoice.
     *
     * @param  array  $discounts  Array of Discount objects or arrays
     * @return static
     * @throws \InvalidArgumentException If invalid discount format is provided
     */
    public function setDiscounts(array $discounts): static
    {
        if (is_array($discounts)) {
            $discountObjects = [];
            foreach ($discounts as $discount) {
                if ($discount instanceof Discount) {
                    $discountObjects[] = $discount;
                } elseif (is_array($discount)) {
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
     * Set the credits for this invoice.
     *
     * @param  array  $credits  Array of Credit objects or arrays
     * @return static
     * @throws \InvalidArgumentException If invalid credit format is provided
     */
    public function setCredits(array $credits): static
    {
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

    // ==================== STATUS METHODS ====================

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
     * Begin payment processing.
     *
     * @return static
     */
    public function beginPayment(): static
    {
        $this->status = self::STATUS_PROCESSING;
        return $this;
    }

    /**
     * Record successful payment.
     *
     * @param  string|null  $paymentId  The payment ID
     * @return static
     */
    public function recordPayment(?string $paymentId = null): static
    {
        $this->status = self::STATUS_SUCCEEDED;
        $this->paymentId = $paymentId;
        $this->lastError = null;
        $this->paidAt = date('Y-m-d H:i:s');
        return $this;
    }

    /**
     * Record payment failure.
     *
     * @param  string  $error  The error message
     * @return static
     */
    public function recordFailure(string $error): static
    {
        $this->status = self::STATUS_FAILED;
        $this->lastError = $error;
        $this->attempts++;
        return $this;
    }

    /**
     * Mark as requiring authentication (e.g., 3D Secure).
     *
     * @param  string  $clientSecret  The client secret for authentication
     * @return static
     */
    public function requireAuthentication(string $clientSecret): static
    {
        $this->status = self::STATUS_REQUIRES_AUTH;
        $this->clientSecret = $clientSecret;
        return $this;
    }

    /**
     * Mark as abandoned.
     *
     * @param  string  $error  The error message
     * @return static
     */
    public function markAsAbandoned(string $error = 'Not found'): static
    {
        $this->status = self::STATUS_ABANDONED;
        $this->lastError = $error;
        return $this;
    }

    /**
     * Mark as disputed.
     *
     * @return static
     */
    public function markAsDisputed(): static
    {
        $this->status = self::STATUS_DISPUTED;
        return $this;
    }

    /**
     * Mark as refunded.
     *
     * @return static
     */
    public function markAsRefunded(): static
    {
        $this->status = self::STATUS_REFUNDED;
        return $this;
    }

    // ==================== STATUS CHECKS ====================

    /**
     * Check if invoice is paid.
     *
     * @return bool
     */
    public function isPaid(): bool
    {
        return $this->status === self::STATUS_SUCCEEDED;
    }

    /**
     * Check if invoice payment failed.
     *
     * @return bool
     */
    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    /**
     * Check if invoice is being processed.
     *
     * @return bool
     */
    public function isProcessing(): bool
    {
        return $this->status === self::STATUS_PROCESSING;
    }

    /**
     * Check if invoice requires payment.
     *
     * @return bool
     */
    public function requiresPayment(): bool
    {
        return in_array($this->status, [
            self::STATUS_DUE,
            self::STATUS_FAILED,
            self::STATUS_REQUIRES_AUTH,
        ], true);
    }

    /**
     * Check if invoice is in a terminal (final) state.
     *
     * @return bool
     */
    public function isTerminal(): bool
    {
        return in_array($this->status, [
            self::STATUS_SUCCEEDED,
            self::STATUS_CANCELLED,
            self::STATUS_ABANDONED,
            self::STATUS_REFUNDED,
        ], true);
    }

    /**
     * Check if invoice requires authentication.
     *
     * @return bool
     */
    public function requiresAuthentication(): bool
    {
        return $this->status === self::STATUS_REQUIRES_AUTH;
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

    // ==================== STATE MACHINE ====================

    /**
     * Check if a state transition is allowed.
     *
     * @param  string  $newStatus  The target status
     * @return bool True if transition is allowed
     */
    public function canTransitionTo(string $newStatus): bool
    {
        $allowed = static::TRANSITIONS[$this->status] ?? [];
        return in_array($newStatus, $allowed, true);
    }

    // ==================== DISCOUNT AND CREDIT OPERATIONS ====================

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

    // ==================== SERIALIZATION ====================

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
            'type' => $this->type,
            'from' => $this->from,
            'to' => $this->to,
            'dueAt' => $this->dueAt,
            'issuedAt' => $this->issuedAt,
            'paidAt' => $this->paidAt,
            'paymentId' => $this->paymentId,
            'clientSecret' => $this->clientSecret,
            'lastError' => $this->lastError,
            'attempts' => $this->attempts,
            'nextAttemptAt' => $this->nextAttemptAt,
        ];
    }

    /**
     * Create an Invoice instance from an array.
     *
     * @param  array  $data  The invoice data array
     * @return static The created Invoice instance
     */
    public static function fromArray(array $data): static
    {
        $id = $data['id'] ?? $data['$id'] ?? uniqid('invoice_');
        $amount = $data['amount'] ?? 0;
        $status = $data['status'] ?? self::STATUS_DRAFT;
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

        $invoice = new static(
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

        // Set additional properties
        $invoice->type = $data['type'] ?? self::TYPE_SUBSCRIPTION;
        $invoice->from = $data['from'] ?? null;
        $invoice->to = $data['to'] ?? null;
        $invoice->dueAt = $data['dueAt'] ?? null;
        $invoice->issuedAt = $data['issuedAt'] ?? null;
        $invoice->paidAt = $data['paidAt'] ?? null;
        $invoice->paymentId = $data['paymentId'] ?? null;
        $invoice->clientSecret = $data['clientSecret'] ?? null;
        $invoice->lastError = $data['lastError'] ?? null;
        $invoice->attempts = $data['attempts'] ?? 0;
        $invoice->nextAttemptAt = $data['nextAttemptAt'] ?? null;

        return $invoice;
    }
}
