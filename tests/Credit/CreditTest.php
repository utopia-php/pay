<?php

namespace Utopia\Pay\Tests\Credit;

use PHPUnit\Framework\TestCase;
use Utopia\Pay\Credit\Credit;

class CreditTest extends TestCase
{
    public function testUsingCreditsReturnsRemainingBalanceAndPreservesOriginal(): void
    {
        $credit = new Credit('credit', 100);
        $remaining = $credit->useCredits(40);
        $this->assertSame(100.0, $credit->credits);
        $this->assertSame(0.0, $credit->creditsUsed);
        $this->assertSame(Credit::STATUS_ACTIVE, $credit->status);
        $this->assertSame(60.0, $remaining->credits);
        $this->assertSame(40.0, $remaining->creditsUsed);
        $this->assertSame(Credit::STATUS_ACTIVE, $remaining->status);

        $exhausted = $remaining->useCredits(100);
        $this->assertSame(60.0, $remaining->credits);
        $this->assertSame(0.0, $exhausted->credits);
        $this->assertSame(100.0, $exhausted->creditsUsed);
        $this->assertSame(Credit::STATUS_APPLIED, $exhausted->status);
        $this->assertTrue($exhausted->isFullyUsed());
        $this->assertFalse($exhausted->hasAvailableCredits());
    }

    public function testEmptyCreditCannotSupplyMoney(): void
    {
        $credit = new Credit('empty', 0, 100);
        $remaining = $credit->useCredits(50);
        $this->assertSame(0.0, $remaining->credits);
        $this->assertSame(100.0, $remaining->creditsUsed);
        $this->assertSame(Credit::STATUS_APPLIED, $remaining->status);
        $this->assertSame(Credit::STATUS_ACTIVE, $credit->status);
    }

    public function testNonPositiveAmountLeavesCreditUnchanged(): void
    {
        $credit = new Credit('credit', 100);
        $this->assertSame($credit, $credit->useCredits(-50));
        $this->assertSame($credit, $credit->useCredits(0));
        $this->assertSame(100.0, $credit->credits);
    }

    public function testSerialization(): void
    {
        $data = ['id' => 'credit', 'credits' => 75.0, 'creditsUsed' => 25.0, 'status' => Credit::STATUS_ACTIVE];
        $this->assertSame($data, Credit::fromArray($data)->toArray());
        $this->assertSame(['id' => 'credit', 'credits' => 100.0, 'creditsUsed' => 0.0, 'status' => Credit::STATUS_ACTIVE], Credit::fromArray(['$id' => 'credit', 'credits' => 100])->toArray());
    }
}
