<?php

namespace Pay\Invoice;

use Pay\Discount\Discount;
use Pay\Credit\Credit;

class Invoice
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_DUE = 'due';
    public const STATUS_REFUNDED = 'refunded';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_SUCCEEDED = 'succeeded';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_FAILED = 'failed';

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
        $this->id = $id;
        $this->amount = $amount;
        $this->currency = $currency;
        $this->status = 'unpaid';
        $this->grossAmount = $amount;
        $this->taxAmount = 0;
        $this->vatAmount = 0;
        $this->address = $address;
        $this->setDiscounts($discounts);
        $this->setCredits($credits);
    }

    public function getid()
    {
        return $this->id;
    }

    public function getAmount()
    {
        return $this->amount;
    }

    public function getCurrency()
    {
        return $this->currency;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function markAsPaid()
    {
        $this->status = 'paid';
    }

    public function getGrossAmount()
    {
        return $this->grossAmount;
    }

    public function setGrossAmount($grossAmount)
    {
        $this->grossAmount = $grossAmount;
        return $this;
    }

    public function getTaxAmount()
    {
        return $this->taxAmount;
    }

    public function setTaxAmount($taxAmount)
    {
        $this->taxAmount = $taxAmount;
        return $this;
    }

    public function getVatAmount()
    {
        return $this->vatAmount;
    }

    public function setVatAmount($vatAmount)
    {
        $this->vatAmount = $vatAmount;
        return $this;
    }

    public function getAddress()
    {
        return $this->address;
    }

    public function setAddress($address)
    {
        $this->address = $address;
        return $this;
    }

    public function getDiscounts()
    {
        return $this->discounts;
    }

    public function setDiscounts($discounts)
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

    public function addDiscount(Discount $discount)
    {
        $this->discounts[] = $discount;
        return $this;
    }

    public function getCreditsUsed()
    {
        return $this->creditsUsed;
    }

    public function setCreditsUsed($creditsUsed)
    {
        $this->creditsUsed = $creditsUsed;
        return $this;
    }

    public function getCreditInternalIds()
    {
        return $this->creditsIds;
    }

    public function setCreditInternalIds($creditsIds)
    {
        $this->creditsIds = $creditsIds;
        return $this;
    }

    public function addCreditInternalId($creditId)
    {
        $this->creditsIds[] = $creditId;
        return $this;
    }

    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    public function markAsDue()
    {
        $this->status = self::STATUS_DUE;
        return $this;
    }

    public function markAsSucceeded()
    {
        $this->status =  self::STATUS_SUCCEEDED;
        return $this;
    }

    public function markAsCancelled()
    {
        $this->status =  self::STATUS_CANCELLED;
        return $this;
    }

    public function isNegativeAmount()
    {
        return $this->amount < 0;
    }

    public function isBelowMinimumAmount($minimumAmount = 0.50)
    {
        return $this->grossAmount < $minimumAmount;
    }

    public function isZeroAmount()
    {
        return $this->grossAmount === 0;
    }

   
    public function getDiscountTotal()
    {
        $total = 0;
        foreach ($this->discounts as $discount) {
            if ($discount instanceof Discount) {
                $total += $discount->getAmount();
            }
        }
        return $total;
    }

    public function getDiscountsAsArray()
    {
        $discountArray = [];
        foreach ($this->discounts as $discount) {
            if ($discount instanceof Discount) {
                $discountArray[] = $discount->toArray();
            }
        }
        return $discountArray;
    }

    public function getCredits()
    {
        return $this->credits;
    }

    public function setCredits(array $credits)
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

    public function addCredit(Credit $credit)
    {
        $this->credits[] = $credit;
        return $this;
    }

    public function getTotalAvailableCredits()
    {
        $total = 0;
        foreach ($this->credits as $credit) {
            $total += $credit->getCredits();
        }
        return $total;
    }

    public function applyCredits()
    {
        $amount = $this->grossAmount;
        $totalCreditsUsed = 0;
        $creditsIds = [];

        foreach ($this->credits as $credit) {
            if ($amount == 0) {
                break;
            }

            $availableCredit = $credit->getCredits();
            if ($amount >= $availableCredit) {
                $amount = $amount - $availableCredit;
                $creditsUsed = $availableCredit;
                $availableCredit = 0;
            } else {
                $availableCredit = $availableCredit - $amount;
                $creditsUsed = $amount;
                $amount = 0;
            }

            $totalCreditsUsed += $creditsUsed;
            $credit->useCredits($creditsUsed);
            $creditsIds[] = $credit->getId();
        }

        $this->setGrossAmount($amount);
        $this->setCreditsUsed($totalCreditsUsed);
        $this->setCreditInternalIds($creditsIds);

        return $this;
    }

    public function applyDiscounts()
    {
        $discounts = $this->discounts;
        $discountObjects = [];
        $amount = $this->grossAmount;

        foreach ($discounts as $discount) {
            // Handle both Discount objects and arrays
            if ($discount instanceof Discount) {
                $discountAmount = $discount->getAmount();
                $discountObjects[] = $discount;
            } else {
                $discountAmount = $discount['amount'] ?? 0;
                $discountDescription = $discount['description'] ?? '';
                $discountObject = new Discount(
                    $discount['id'] ?? uniqid('discount_'),
                    $discount['value'] ?? $discountAmount,
                    $discountAmount,
                    $discountDescription,
                    $discount['type'] ?? Discount::TYPE_FIXED
                );
                $discountObjects[] = $discountObject;
            }

            if ($discountAmount > 0) {
                $amount -= $discountAmount;
                if ($amount < 0) {
                    $amount = 0;
                }
            }
        }

        $this->setDiscounts($discountObjects);
        $this->setGrossAmount($amount);
        return $this;
    }

    public function finalizeInvoice()
    {
        // Apply discounts first
        $this->applyDiscounts();
        
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

    public function hasDiscounts()
    {
        return !empty($this->discounts);
    }

    public function hasCredits()
    {
        return !empty($this->credits);
    }

    public function getDiscountCount()
    {
        return count($this->discounts);
    }

    public function getCreditCount()
    {
        return count($this->credits);
    }

    public function clearDiscounts()
    {
        $this->discounts = [];
        return $this;
    }

    public function clearCredits()
    {
        $this->credits = [];
        return $this;
    }

    public function getCreditsAsArray()
    {
        $creditsArray = [];
        foreach ($this->credits as $credit) {
            if ($credit instanceof Credit) {
                $creditsArray[] = [
                    'id' => $credit->getId(),
                    'credits' => $credit->getCredits(),
                    'creditsUsed' => $credit->getCreditsUsed(),
                    'status' => $credit->getStatus()
                ];
            }
        }
        return $creditsArray;
    }

    public function findDiscountById(string $id)
    {
        foreach ($this->discounts as $discount) {
            if ($discount->getId() === $id) {
                return $discount;
            }
        }
        return null;
    }

    public function findCreditById(string $id)
    {
        foreach ($this->credits as $credit) {
            if ($credit->getId() === $id) {
                return $credit;
            }
        }
        return null;
    }

    public function removeDiscountById(string $id)
    {
        $this->discounts = array_filter($this->discounts, function($discount) use ($id) {
            return $discount->getId() !== $id;
        });
        return $this;
    }
}