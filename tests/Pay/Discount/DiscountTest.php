<?php

namespace Utopia\Tests;

use PHPUnit\Framework\TestCase;
use Utopia\Pay\Discount\Discount;

class DiscountTest extends TestCase
{
    private Discount $fixedDiscount;

    private Discount $percentageDiscount;

    private string $discountId = 'discount-123';

    private float $fixedValue = 25.0;

    private float $percentageValue = 10.0; // 10%

    private string $description = 'Test Discount';

    protected function setUp(): void
    {
        $this->fixedDiscount = new Discount(
            $this->discountId,
            $this->fixedValue,
            $this->description,
            Discount::TYPE_FIXED
        );

        $this->percentageDiscount = new Discount(
            'discount-456',
            $this->percentageValue,
            'Percentage Discount',
            Discount::TYPE_PERCENTAGE
        );
    }

    public function testConstructor(): void
    {
        $this->assertEquals($this->discountId, $this->fixedDiscount->getId());
        $this->assertEquals($this->fixedValue, $this->fixedDiscount->getValue());
        $this->assertEquals($this->description, $this->fixedDiscount->getDescription());
        $this->assertEquals(Discount::TYPE_FIXED, $this->fixedDiscount->getType());
    }

    public function testGettersAndSetters(): void
    {
        $newId = 'discount-789';
        $newDiscountValue = 50.0;
        $newDescription = 'Updated Discount';
        $newType = Discount::TYPE_PERCENTAGE;

        $this->fixedDiscount->setId($newId);
        $this->fixedDiscount->setValue($newDiscountValue);
        $this->fixedDiscount->setDescription($newDescription);
        $this->fixedDiscount->setType($newType);

        $this->assertEquals($newId, $this->fixedDiscount->getId());
        $this->assertEquals($newDiscountValue, $this->fixedDiscount->getValue());
        $this->assertEquals($newDescription, $this->fixedDiscount->getDescription());
        $this->assertEquals($newType, $this->fixedDiscount->getType());
    }

    public function testCalculateDiscountFixed(): void
    {
        $invoiceAmount = 100.0;
        $discountAmount = $this->fixedDiscount->calculateDiscount($invoiceAmount);

        // For fixed type, it uses the minimum of the discount value and invoice amount
        $this->assertEquals(min($this->fixedValue, $invoiceAmount), $discountAmount);
    }

    public function testCalculateDiscountFixedWithLowerInvoiceAmount(): void
    {
        $invoiceAmount = 20.0;
        $discountAmount = $this->fixedDiscount->calculateDiscount($invoiceAmount);

        // Fixed discount should be capped at invoice amount
        $this->assertEquals($invoiceAmount, $discountAmount);
    }

    public function testCalculateDiscountPercentage(): void
    {
        $invoiceAmount = 200.0;
        $expectedDiscount = $invoiceAmount * ($this->percentageValue / 100);
        $discountAmount = $this->percentageDiscount->calculateDiscount($invoiceAmount);

        $this->assertEquals($expectedDiscount, $discountAmount);
    }

    public function testCalculateDiscountWithZeroInvoiceAmount(): void
    {
        $invoiceAmount = 0.0;

        $fixedDiscountAmount = $this->fixedDiscount->calculateDiscount($invoiceAmount);
        $percentageDiscountAmount = $this->percentageDiscount->calculateDiscount($invoiceAmount);

        $this->assertEquals(0, $fixedDiscountAmount);
        $this->assertEquals(0, $percentageDiscountAmount);
    }

    public function testCalculateDiscountWithNegativeInvoiceAmount(): void
    {
        $invoiceAmount = -50.0;

        // Assuming the implementation should handle negative amounts safely
        // Adjust based on the expected behavior in your application
        $fixedDiscountAmount = max(0, $this->fixedDiscount->calculateDiscount($invoiceAmount));
        $percentageDiscountAmount = max(0, $this->percentageDiscount->calculateDiscount($invoiceAmount));

        $this->assertEquals(0, $fixedDiscountAmount);
        $this->assertEquals(0, $percentageDiscountAmount);
    }

    public function testToArray(): void
    {
        $array = $this->fixedDiscount->toArray();

        $this->assertIsArray($array);
        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('value', $array);
        $this->assertArrayHasKey('description', $array);
        $this->assertArrayHasKey('type', $array);

        $this->assertEquals($this->discountId, $array['id']);
        $this->assertEquals($this->fixedValue, $array['value']);
        $this->assertEquals($this->description, $array['description']);
        $this->assertEquals(Discount::TYPE_FIXED, $array['type']);
    }

    public function testFromArray(): void
    {
        $data = [
            'id' => 'discount-789',
            'value' => 30.0,
            'description' => 'From Array Discount',
            'type' => Discount::TYPE_FIXED,
        ];

        $discount = Discount::fromArray($data);

        $this->assertEquals($data['id'], $discount->getId());
        $this->assertEquals($data['value'], $discount->getValue());
        $this->assertEquals($data['description'], $discount->getDescription());
        $this->assertEquals($data['type'], $discount->getType());
    }

    public function testFromArrayWithMinimalData(): void
    {
        $data = [
            'id' => 'discount-789',
            'value' => 30.0,
        ];

        $discount = Discount::fromArray($data);

        $this->assertEquals($data['id'], $discount->getId());
        $this->assertEquals($data['value'], $discount->getValue());
        $this->assertEquals('', $discount->getDescription());
        $this->assertEquals(Discount::TYPE_FIXED, $discount->getType());
    }

    

    public function testNegativeDiscountValueHandling(): void
    {
        // Test that negative values throw an exception in constructor
        try {
            new Discount(
                'negative-discount',
                -10.0,
                'Negative test',
                Discount::TYPE_FIXED
            );
            $this->fail('Expected InvalidArgumentException was not thrown');
        } catch (\InvalidArgumentException $e) {
            $this->assertEquals('Discount value cannot be negative', $e->getMessage());
        }
    }

    public function testSetNegativeDiscountValue(): void
    {
        // Test that setting negative value throws an exception
        try {
            $this->fixedDiscount->setValue(-20.0);
            $this->fail('Expected InvalidArgumentException was not thrown');
        } catch (\InvalidArgumentException $e) {
            $this->assertEquals('Discount value cannot be negative', $e->getMessage());
        }
    }

    public function testFromArrayWithNegativeValue(): void
    {
        // Test that fromArray throws exception for negative values
        $data = [
            'id' => 'discount-negative',
            'value' => -10.0,
            'type' => Discount::TYPE_FIXED,
        ];

        try {
            Discount::fromArray($data);
            $this->fail('Expected InvalidArgumentException was not thrown');
        } catch (\InvalidArgumentException $e) {
            $this->assertEquals('Discount value cannot be negative', $e->getMessage());
        }
    }

    public function testFromArrayWithNullValue(): void
    {
        // Test that fromArray throws exception for null values
        $data = [
            'id' => 'discount-null',
            'type' => Discount::TYPE_FIXED,
        ];

        try {
            Discount::fromArray($data);
            $this->fail('Expected InvalidArgumentException was not thrown');
        } catch (\InvalidArgumentException $e) {
            $this->assertEquals('Discount value cannot be null', $e->getMessage());
        }
    }
}
