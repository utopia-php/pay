<?php

namespace Utopia\Pay\Tests\Discount;

use PHPUnit\Framework\TestCase;
use Utopia\Pay\Discount\Discount;

class DiscountTest extends TestCase
{
    public function testFixedDiscountIsCappedAtTheInvoiceAmount(): void
    {
        $discount = new Discount('fixed', 25);
        $this->assertSame(25.0, $discount->calculateDiscount(100));
        $this->assertSame(20.0, $discount->calculateDiscount(20));
        $this->assertSame(0.0, $discount->calculateDiscount(0));
        $this->assertSame(0.0, $discount->calculateDiscount(-50));
    }

    public function testPercentageDiscount(): void
    {
        $discount = new Discount('percentage', 10, type: Discount::TYPE_PERCENTAGE);
        $this->assertSame(20.0, $discount->calculateDiscount(200));
        $this->assertSame(0.0, $discount->calculateDiscount(0));
        $this->assertSame(0.0, $discount->calculateDiscount(-50));
    }

    public function testSerialization(): void
    {
        $data = ['id' => 'discount', 'value' => 30.0, 'description' => 'Discount', 'type' => Discount::TYPE_FIXED];
        $this->assertSame($data, Discount::fromArray($data)->toArray());
        $this->assertSame(25.0, Discount::fromArray(['$id' => 'discount', 'value' => 25])->calculateDiscount(100));
    }

    public function testNegativeValueIsRejectedAtConstruction(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Discount('negative', -10);
    }

    public function testMissingValueIsRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Discount::fromArray(['id' => 'missing']);
    }
}
