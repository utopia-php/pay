<?php

namespace Utopia\Pay;

/**
 * Currency helper class for working with currencies.
 *
 * Provides currency validation, conversion helpers, and common currency codes.
 */
class Currency
{
    // Major world currencies
    public const USD = 'USD'; // US Dollar

    public const EUR = 'EUR'; // Euro

    public const GBP = 'GBP'; // British Pound

    public const JPY = 'JPY'; // Japanese Yen

    public const CNY = 'CNY'; // Chinese Yuan

    public const CHF = 'CHF'; // Swiss Franc

    public const AUD = 'AUD'; // Australian Dollar

    public const CAD = 'CAD'; // Canadian Dollar

    public const NZD = 'NZD'; // New Zealand Dollar

    public const HKD = 'HKD'; // Hong Kong Dollar

    public const SGD = 'SGD'; // Singapore Dollar

    // European currencies
    public const SEK = 'SEK'; // Swedish Krona

    public const NOK = 'NOK'; // Norwegian Krone

    public const DKK = 'DKK'; // Danish Krone

    public const PLN = 'PLN'; // Polish Zloty

    public const CZK = 'CZK'; // Czech Koruna

    public const HUF = 'HUF'; // Hungarian Forint

    public const RON = 'RON'; // Romanian Leu

    public const BGN = 'BGN'; // Bulgarian Lev

    // Americas
    public const MXN = 'MXN'; // Mexican Peso

    public const BRL = 'BRL'; // Brazilian Real

    public const ARS = 'ARS'; // Argentine Peso

    public const CLP = 'CLP'; // Chilean Peso

    public const COP = 'COP'; // Colombian Peso

    // Asia & Pacific
    public const INR = 'INR'; // Indian Rupee

    public const KRW = 'KRW'; // South Korean Won

    public const THB = 'THB'; // Thai Baht

    public const IDR = 'IDR'; // Indonesian Rupiah

    public const MYR = 'MYR'; // Malaysian Ringgit

    public const PHP = 'PHP'; // Philippine Peso

    public const TWD = 'TWD'; // Taiwan Dollar

    public const VND = 'VND'; // Vietnamese Dong

    // Middle East & Africa
    public const AED = 'AED'; // UAE Dirham

    public const SAR = 'SAR'; // Saudi Riyal

    public const ILS = 'ILS'; // Israeli Shekel

    public const TRY = 'TRY'; // Turkish Lira

    public const ZAR = 'ZAR'; // South African Rand

    public const EGP = 'EGP'; // Egyptian Pound

    /**
     * Zero-decimal currencies (amounts are in whole units, not cents).
     *
     * @var array<string>
     */
    private static array $zeroDecimalCurrencies = [
        'BIF', 'CLP', 'DJF', 'GNF', 'JPY', 'KMF', 'KRW', 'MGA',
        'PYG', 'RWF', 'UGX', 'VND', 'VUV', 'XAF', 'XOF', 'XPF',
    ];

    /**
     * Three-decimal currencies.
     *
     * @var array<string>
     */
    private static array $threeDecimalCurrencies = [
        'BHD', 'JOD', 'KWD', 'OMR', 'TND',
    ];

    /**
     * List of valid ISO 4217 currency codes supported by major payment processors.
     *
     * @var array<string>
     */
    private static array $validCurrencies = [
        'AED', 'AFN', 'ALL', 'AMD', 'ANG', 'AOA', 'ARS', 'AUD', 'AWG', 'AZN',
        'BAM', 'BBD', 'BDT', 'BGN', 'BHD', 'BIF', 'BMD', 'BND', 'BOB', 'BRL',
        'BSD', 'BTN', 'BWP', 'BYN', 'BZD', 'CAD', 'CDF', 'CHF', 'CLP', 'CNY',
        'COP', 'CRC', 'CUP', 'CVE', 'CZK', 'DJF', 'DKK', 'DOP', 'DZD', 'EGP',
        'ERN', 'ETB', 'EUR', 'FJD', 'FKP', 'GBP', 'GEL', 'GHS', 'GIP', 'GMD',
        'GNF', 'GTQ', 'GYD', 'HKD', 'HNL', 'HRK', 'HTG', 'HUF', 'IDR', 'ILS',
        'INR', 'IQD', 'IRR', 'ISK', 'JMD', 'JOD', 'JPY', 'KES', 'KGS', 'KHR',
        'KMF', 'KPW', 'KRW', 'KWD', 'KYD', 'KZT', 'LAK', 'LBP', 'LKR', 'LRD',
        'LSL', 'LYD', 'MAD', 'MDL', 'MGA', 'MKD', 'MMK', 'MNT', 'MOP', 'MRU',
        'MUR', 'MVR', 'MWK', 'MXN', 'MYR', 'MZN', 'NAD', 'NGN', 'NIO', 'NOK',
        'NPR', 'NZD', 'OMR', 'PAB', 'PEN', 'PGK', 'PHP', 'PKR', 'PLN', 'PYG',
        'QAR', 'RON', 'RSD', 'RUB', 'RWF', 'SAR', 'SBD', 'SCR', 'SDG', 'SEK',
        'SGD', 'SHP', 'SLL', 'SOS', 'SRD', 'SSP', 'STN', 'SVC', 'SYP', 'SZL',
        'THB', 'TJS', 'TMT', 'TND', 'TOP', 'TRY', 'TTD', 'TWD', 'TZS', 'UAH',
        'UGX', 'USD', 'UYU', 'UZS', 'VES', 'VND', 'VUV', 'WST', 'XAF', 'XCD',
        'XOF', 'XPF', 'YER', 'ZAR', 'ZMW', 'ZWL',
    ];

    /**
     * Check if a currency code is valid.
     *
     * @param  string  $currency  The three-letter currency code
     * @return bool True if valid ISO 4217 currency code
     */
    public static function isValid(string $currency): bool
    {
        return in_array(strtoupper($currency), self::$validCurrencies);
    }

    /**
     * Check if a currency is a zero-decimal currency.
     *
     * Zero-decimal currencies don't use minor units (cents).
     * For example, JPY amounts are in whole yen, not sen.
     *
     * @param  string  $currency  The three-letter currency code
     * @return bool True if zero-decimal currency
     */
    public static function isZeroDecimal(string $currency): bool
    {
        return in_array(strtoupper($currency), self::$zeroDecimalCurrencies);
    }

    /**
     * Check if a currency uses three decimal places.
     *
     * @param  string  $currency  The three-letter currency code
     * @return bool True if three-decimal currency
     */
    public static function isThreeDecimal(string $currency): bool
    {
        return in_array(strtoupper($currency), self::$threeDecimalCurrencies);
    }

    /**
     * Get the number of decimal places for a currency.
     *
     * @param  string  $currency  The three-letter currency code
     * @return int Number of decimal places (0, 2, or 3)
     */
    public static function getDecimalPlaces(string $currency): int
    {
        $currency = strtoupper($currency);

        if (self::isZeroDecimal($currency)) {
            return 0;
        }

        if (self::isThreeDecimal($currency)) {
            return 3;
        }

        return 2;
    }

    /**
     * Convert a decimal amount to the smallest currency unit.
     *
     * For example, $10.50 USD becomes 1050 (cents).
     * For zero-decimal currencies like JPY, 1000 stays 1000.
     *
     * @param  float  $amount  The decimal amount
     * @param  string  $currency  The three-letter currency code
     * @return int The amount in smallest currency unit
     */
    public static function toSmallestUnit(float $amount, string $currency): int
    {
        $decimals = self::getDecimalPlaces($currency);
        $multiplier = pow(10, $decimals);

        return (int) round($amount * $multiplier);
    }

    /**
     * Convert from smallest currency unit to decimal amount.
     *
     * For example, 1050 cents becomes $10.50 USD.
     *
     * @param  int  $amount  The amount in smallest currency unit
     * @param  string  $currency  The three-letter currency code
     * @return float The decimal amount
     */
    public static function fromSmallestUnit(int $amount, string $currency): float
    {
        $decimals = self::getDecimalPlaces($currency);
        $divisor = pow(10, $decimals);

        return round($amount / $divisor, $decimals);
    }

    /**
     * Format an amount for display.
     *
     * @param  int  $amount  The amount in smallest currency unit
     * @param  string  $currency  The three-letter currency code
     * @param  string|null  $locale  The locale for formatting (default: en_US)
     * @return string The formatted amount string
     */
    public static function format(int $amount, string $currency, ?string $locale = null): string
    {
        $decimalAmount = self::fromSmallestUnit($amount, $currency);
        $decimals = self::getDecimalPlaces($currency);
        $currency = strtoupper($currency);

        // Simple formatting without locale support for portability
        $formatted = number_format($decimalAmount, $decimals, '.', ',');

        return $currency.' '.$formatted;
    }

    /**
     * Get the currency symbol.
     *
     * @param  string  $currency  The three-letter currency code
     * @return string The currency symbol
     */
    public static function getSymbol(string $currency): string
    {
        $symbols = [
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'JPY' => '¥',
            'CNY' => '¥',
            'CHF' => 'CHF',
            'AUD' => 'A$',
            'CAD' => 'C$',
            'NZD' => 'NZ$',
            'HKD' => 'HK$',
            'SGD' => 'S$',
            'SEK' => 'kr',
            'NOK' => 'kr',
            'DKK' => 'kr',
            'PLN' => 'zł',
            'CZK' => 'Kč',
            'HUF' => 'Ft',
            'INR' => '₹',
            'KRW' => '₩',
            'THB' => '฿',
            'MXN' => 'MX$',
            'BRL' => 'R$',
            'ILS' => '₪',
            'TRY' => '₺',
            'ZAR' => 'R',
            'RUB' => '₽',
        ];

        return $symbols[strtoupper($currency)] ?? $currency;
    }

    /**
     * Validate that an amount meets the minimum for a currency.
     *
     * Most payment processors have minimum amounts (e.g., $0.50 for Stripe).
     *
     * @param  int  $amount  The amount in smallest currency unit
     * @param  string  $currency  The three-letter currency code
     * @param  int  $minimumCents  The minimum amount in cents (default: 50)
     * @return bool True if amount meets minimum
     */
    public static function meetsMinimum(int $amount, string $currency, int $minimumCents = 50): bool
    {
        // Adjust minimum for zero-decimal currencies
        if (self::isZeroDecimal($currency)) {
            // For zero-decimal currencies, minimum is typically 1 unit
            return $amount >= 1;
        }

        return $amount >= $minimumCents;
    }

    /**
     * Get all valid currency codes.
     *
     * @return array<string> Array of valid currency codes
     */
    public static function getAllCurrencies(): array
    {
        return self::$validCurrencies;
    }

    /**
     * Get commonly used currencies.
     *
     * @return array<string> Array of common currency codes
     */
    public static function getCommonCurrencies(): array
    {
        return [
            self::USD, self::EUR, self::GBP, self::JPY, self::CAD,
            self::AUD, self::CHF, self::CNY, self::INR, self::MXN,
            self::BRL, self::SGD, self::HKD, self::NZD, self::SEK,
        ];
    }
}
