<?php

namespace Utopia\Tests;

use PHPUnit\Framework\TestCase;
use Utopia\Pay\Currency;

class CurrencyTest extends TestCase
{
    public function testIsValid(): void
    {
        $this->assertTrue(Currency::isValid('USD'));
        $this->assertTrue(Currency::isValid('usd'));
        $this->assertTrue(Currency::isValid('EUR'));
        $this->assertTrue(Currency::isValid('GBP'));
        $this->assertTrue(Currency::isValid('JPY'));

        $this->assertFalse(Currency::isValid('XXX'));
        $this->assertFalse(Currency::isValid('INVALID'));
        $this->assertFalse(Currency::isValid(''));
    }

    public function testIsZeroDecimal(): void
    {
        $this->assertTrue(Currency::isZeroDecimal('JPY'));
        $this->assertTrue(Currency::isZeroDecimal('jpy'));
        $this->assertTrue(Currency::isZeroDecimal('KRW'));
        $this->assertTrue(Currency::isZeroDecimal('VND'));

        $this->assertFalse(Currency::isZeroDecimal('USD'));
        $this->assertFalse(Currency::isZeroDecimal('EUR'));
        $this->assertFalse(Currency::isZeroDecimal('GBP'));
    }

    public function testIsThreeDecimal(): void
    {
        $this->assertTrue(Currency::isThreeDecimal('BHD'));
        $this->assertTrue(Currency::isThreeDecimal('KWD'));
        $this->assertTrue(Currency::isThreeDecimal('OMR'));

        $this->assertFalse(Currency::isThreeDecimal('USD'));
        $this->assertFalse(Currency::isThreeDecimal('EUR'));
        $this->assertFalse(Currency::isThreeDecimal('JPY'));
    }

    public function testGetDecimalPlaces(): void
    {
        $this->assertEquals(2, Currency::getDecimalPlaces('USD'));
        $this->assertEquals(2, Currency::getDecimalPlaces('EUR'));
        $this->assertEquals(2, Currency::getDecimalPlaces('GBP'));

        $this->assertEquals(0, Currency::getDecimalPlaces('JPY'));
        $this->assertEquals(0, Currency::getDecimalPlaces('KRW'));

        $this->assertEquals(3, Currency::getDecimalPlaces('BHD'));
        $this->assertEquals(3, Currency::getDecimalPlaces('KWD'));
    }

    public function testToSmallestUnit(): void
    {
        // Two-decimal currencies
        $this->assertEquals(1000, Currency::toSmallestUnit(10.00, 'USD'));
        $this->assertEquals(1050, Currency::toSmallestUnit(10.50, 'USD'));
        $this->assertEquals(999, Currency::toSmallestUnit(9.99, 'EUR'));

        // Zero-decimal currencies
        $this->assertEquals(1000, Currency::toSmallestUnit(1000, 'JPY'));
        $this->assertEquals(5000, Currency::toSmallestUnit(5000, 'KRW'));

        // Three-decimal currencies
        $this->assertEquals(10000, Currency::toSmallestUnit(10.000, 'BHD'));
        $this->assertEquals(10500, Currency::toSmallestUnit(10.500, 'KWD'));
    }

    public function testFromSmallestUnit(): void
    {
        // Two-decimal currencies
        $this->assertEquals(10.00, Currency::fromSmallestUnit(1000, 'USD'));
        $this->assertEquals(10.50, Currency::fromSmallestUnit(1050, 'USD'));
        $this->assertEquals(9.99, Currency::fromSmallestUnit(999, 'EUR'));

        // Zero-decimal currencies
        $this->assertEquals(1000, Currency::fromSmallestUnit(1000, 'JPY'));
        $this->assertEquals(5000, Currency::fromSmallestUnit(5000, 'KRW'));

        // Three-decimal currencies
        $this->assertEquals(10.000, Currency::fromSmallestUnit(10000, 'BHD'));
        $this->assertEquals(10.500, Currency::fromSmallestUnit(10500, 'KWD'));
    }

    public function testFormat(): void
    {
        $this->assertEquals('USD 10.00', Currency::format(1000, 'USD'));
        $this->assertEquals('EUR 15.50', Currency::format(1550, 'EUR'));
        $this->assertEquals('JPY 1,000', Currency::format(1000, 'JPY'));
        $this->assertEquals('BHD 10.500', Currency::format(10500, 'BHD'));
    }

    public function testGetSymbol(): void
    {
        $this->assertEquals('$', Currency::getSymbol('USD'));
        $this->assertEquals('€', Currency::getSymbol('EUR'));
        $this->assertEquals('£', Currency::getSymbol('GBP'));
        $this->assertEquals('¥', Currency::getSymbol('JPY'));
        $this->assertEquals('¥', Currency::getSymbol('CNY'));
        $this->assertEquals('₹', Currency::getSymbol('INR'));
        $this->assertEquals('₩', Currency::getSymbol('KRW'));
        $this->assertEquals('R$', Currency::getSymbol('BRL'));

        // Unknown currency returns code
        $this->assertEquals('ZWL', Currency::getSymbol('ZWL'));
    }

    public function testMeetsMinimum(): void
    {
        // Two-decimal currencies
        $this->assertTrue(Currency::meetsMinimum(50, 'USD'));
        $this->assertTrue(Currency::meetsMinimum(100, 'USD'));
        $this->assertFalse(Currency::meetsMinimum(49, 'USD'));

        // Custom minimum
        $this->assertTrue(Currency::meetsMinimum(100, 'USD', 100));
        $this->assertFalse(Currency::meetsMinimum(99, 'USD', 100));

        // Zero-decimal currencies
        $this->assertTrue(Currency::meetsMinimum(1, 'JPY'));
        $this->assertFalse(Currency::meetsMinimum(0, 'JPY'));
    }

    public function testGetAllCurrencies(): void
    {
        $currencies = Currency::getAllCurrencies();

        $this->assertIsArray($currencies);
        $this->assertContains('USD', $currencies);
        $this->assertContains('EUR', $currencies);
        $this->assertContains('GBP', $currencies);
        $this->assertContains('JPY', $currencies);
        $this->assertGreaterThan(100, count($currencies));
    }

    public function testGetCommonCurrencies(): void
    {
        $currencies = Currency::getCommonCurrencies();

        $this->assertIsArray($currencies);
        $this->assertContains('USD', $currencies);
        $this->assertContains('EUR', $currencies);
        $this->assertContains('GBP', $currencies);
        $this->assertContains('JPY', $currencies);
        $this->assertCount(15, $currencies);
    }

    public function testCurrencyConstants(): void
    {
        $this->assertEquals('USD', Currency::USD);
        $this->assertEquals('EUR', Currency::EUR);
        $this->assertEquals('GBP', Currency::GBP);
        $this->assertEquals('JPY', Currency::JPY);
        $this->assertEquals('CNY', Currency::CNY);
        $this->assertEquals('CHF', Currency::CHF);
        $this->assertEquals('AUD', Currency::AUD);
        $this->assertEquals('CAD', Currency::CAD);
        $this->assertEquals('INR', Currency::INR);
        $this->assertEquals('BRL', Currency::BRL);
    }

    public function testRoundTripConversion(): void
    {
        // Test that converting to smallest unit and back gives the same value
        $amounts = [10.00, 15.50, 99.99, 0.01, 1000.00];

        foreach ($amounts as $amount) {
            $smallest = Currency::toSmallestUnit($amount, 'USD');
            $result = Currency::fromSmallestUnit($smallest, 'USD');
            $this->assertEquals($amount, $result, "Round-trip failed for amount: $amount");
        }

        // Test with zero-decimal currency
        foreach ([100, 500, 1000, 10000] as $amount) {
            $smallest = Currency::toSmallestUnit($amount, 'JPY');
            $result = Currency::fromSmallestUnit($smallest, 'JPY');
            $this->assertEquals($amount, $result, "Round-trip failed for JPY amount: $amount");
        }
    }
}
