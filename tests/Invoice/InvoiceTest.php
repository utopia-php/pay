<?php

declare(strict_types=1);

namespace Utopia\Pay\Tests\Invoice;

use PHPUnit\Framework\TestCase;
use Utopia\Pay\Credit\Credit;
use Utopia\Pay\Discount\Discount;
use Utopia\Pay\Invoice\Invoice;

class InvoiceTest extends TestCase
{
    public function testFinalizationReturnsANewInvoiceWithoutChangingSharedCredits(): void
    {
        $credit = new Credit('credit', 50);
        $draft = new Invoice('invoice', 100, discounts: [new Discount('fixed', 25)], credits: [$credit]);
        $before = $draft->toArray();
        $final = $draft->finalize();

        $this->assertSame($before, $draft->toArray());
        $this->assertNotSame($draft, $final);
        $this->assertSame(50.0, $credit->credits);
        $this->assertSame(0.0, $credit->creditsUsed);
        $this->assertSame(25.0, $final->grossAmount);
        $this->assertSame(25.0, $final->discountTotal);
        $this->assertSame(50.0, $final->creditsUsed);
        $this->assertSame(['credit'], $final->creditsIds);
        $this->assertSame(\Utopia\Pay\Invoice\Status::Due, $final->status);
        $this->assertSame(\Utopia\Pay\Credit\Status::Applied, $final->credits[0]->status);
        $this->assertSame(0.0, $final->credits[0]->credits);
        $this->assertSame($final->toArray(), $draft->finalize()->toArray());
    }

    public function testFixedDiscountsPrecedePercentageThenTaxesThenCredits(): void
    {
        $invoice = new Invoice('invoice', 100, discounts: [
            new Discount('percentage', 10, type: \Utopia\Pay\Discount\Type::Percentage),
            new Discount('fixed', 25),
        ], credits: [new Credit('credit', 10)], taxAmount: 5.005, vatAmount: 2.004);
        $final = $invoice->finalize();
        $this->assertSame(64.51, $final->grossAmount);
        $this->assertSame(32.5, $final->discountTotal);
        $this->assertSame(5.01, $final->taxAmount);
        $this->assertSame(2.0, $final->vatAmount);
        $this->assertSame(10.0, $final->creditsUsed);
        $this->assertSame(5.005, $invoice->taxAmount);
    }

    public function testCreditsAreUsedInOrderAndRetainUnusedBalances(): void
    {
        $first = new Credit('first', 30);
        $second = new Credit('second', 100);
        $third = new Credit('third', 40);
        $final = new Invoice('invoice', 80, credits: [$first, $second, $third])->finalize();
        $this->assertSame(0.0, $final->grossAmount);
        $this->assertSame(\Utopia\Pay\Invoice\Status::Succeeded, $final->status);
        $this->assertSame(80.0, $final->creditsUsed);
        $this->assertSame(['first', 'second'], $final->creditsIds);
        $this->assertSame(0.0, $final->credits[0]->credits);
        $this->assertSame(50.0, $final->credits[1]->credits);
        $this->assertSame($third, $final->credits[2]);
        $this->assertSame(30.0, $first->credits);
        $this->assertSame(100.0, $second->credits);
    }

    public function testEmptyCreditsDoNotForgiveAnInvoice(): void
    {
        $final = new Invoice('invoice', 80, credits: [new Credit('empty', 0), new Credit('partial', 30)])->finalize();
        $this->assertSame(50.0, $final->grossAmount);
        $this->assertSame(\Utopia\Pay\Invoice\Status::Due, $final->status);
        $this->assertSame(30.0, $final->creditsUsed);
        $this->assertSame(['partial'], $final->creditsIds);
    }

    public function testZeroAndBelowMinimumAmountsFinalizeWithoutStatusRepair(): void
    {
        $zero = new Invoice('zero', 25, discounts: [new Discount('fixed', 25)])->finalize();
        $this->assertTrue($zero->isZeroAmount());
        $this->assertSame(\Utopia\Pay\Invoice\Status::Succeeded, $zero->status);
        $small = new Invoice('small', 0.49)->finalize();
        $this->assertTrue($small->isBelowMinimumAmount());
        $this->assertSame(\Utopia\Pay\Invoice\Status::Cancelled, $small->status);
        $minimum = new Invoice('minimum', 0.50)->finalize();
        $this->assertFalse($minimum->isBelowMinimumAmount());
        $this->assertSame(\Utopia\Pay\Invoice\Status::Due, $minimum->status);
    }

    public function testAbsentAddressSurvivesStorageAndFinalization(): void
    {
        $saved = new Invoice('invoice', 100)->toArray();
        $loaded = Invoice::fromArray($saved);
        $this->assertSame($saved, $loaded->toArray());
        $final = $loaded->finalize()->toArray();
        $this->assertSame($final, Invoice::fromArray($final)->toArray());
    }

    public function testArrayInputAndSerialization(): void
    {
        $data = [
            'id' => 'invoice', 'amount' => 100.0, 'status' => \Utopia\Pay\Invoice\Status::Pending->value, 'currency' => 'EUR',
            'grossAmount' => 0.0, 'taxAmount' => 5.0, 'vatAmount' => 0.0, 'address' => ['city' => '', 'country' => 'DE', 'line1' => null, 'line2' => null, 'postal_code' => null, 'state' => null],
            'discounts' => [['id' => 'discount', 'value' => 25.0, 'description' => 'Discount', 'type' => \Utopia\Pay\Discount\Type::Fixed->value]],
            'credits' => [['id' => 'credit', 'credits' => 50.0, 'creditsUsed' => 0.0, 'status' => \Utopia\Pay\Credit\Status::Active->value]],
            'creditsUsed' => 0.0, 'creditsIds' => [], 'discountTotal' => 0.0,
        ];
        $invoice = Invoice::fromArray($data);
        $this->assertSame($data, $invoice->toArray());
        $this->assertSame(30.0, $invoice->finalize()->grossAmount);
        $this->assertSame(\Utopia\Pay\Invoice\Status::Pending, $invoice->status);
    }
}
