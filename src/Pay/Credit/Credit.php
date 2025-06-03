<?php

namespace Pay\Credit;

class Credit
{
    public const STATUS_ACTIVE = 'active';
    public const STATUS_APPLIED = 'applied';
    public const STATUS_EXPIRED = 'expired';

    public function __construct(private string $id, private float $credits, private float $creditsUsed = 0, private string $status = self::STATUS_ACTIVE)
    {
        
    }


    public function getStatus()
    {
        return $this->status;
    }

    public function markAsApplied()
    {
        $this->status = 'applied';
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function getCredits()
    {
        return $this->credits;
    }

    public function setCredits($credits)
    {
        $this->credits = $credits;
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

    public function getAvailableCredits()
    {
        return $this->credits;
    }

    public function hasAvailableCredits()
    {
        return $this->credits > 0;
    }

    public function useCredits($amount)
    {
        if ($amount <= 0) {
            return 0;
        }

        $creditsToUse = min($amount, $this->credits);
        $this->credits -= $creditsToUse;
        $this->creditsUsed += $creditsToUse;

        if ($this->credits === 0) {
            $this->status =  self::STATUS_APPLIED;
        }

        return $creditsToUse;
    }

    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    public function isFullyUsed()
    {
        return $this->credits === 0 || $this->status ===  self::STATUS_APPLIED;
    }

    public function getRemainingCredits()
    {
        return $this->credits;
    }

    public static function fromArray(array $data)
    {
        return new self($data['id'] ?? $data['$id'] ?? '', 
            $data['credits'] ?? 0.0, 
            $data['creditsUsed'] ?? 0.0, 
            $data['status'] ?? self::STATUS_ACTIVE
        );
    }

    public function toArray()
    {
        return [
            'id' => $this->id,
            'credits' => $this->credits,
            'creditsUsed' => $this->creditsUsed,
            'status' => $this->status
        ];
    }
}