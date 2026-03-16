<?php

namespace Utopia\Tests;

use PHPUnit\Framework\TestCase;
use Utopia\Pay\Idempotency\IdempotencyKey;

class IdempotencyKeyTest extends TestCase
{
    public function testConstructor(): void
    {
        $key = new IdempotencyKey('test_key_123');

        $this->assertEquals('test_key_123', $key->getKey());
        $this->assertNotNull($key->getCreatedAt());
    }

    public function testConstructorWithTimestamp(): void
    {
        $key = new IdempotencyKey('test_key', 1234567890);

        $this->assertEquals('test_key', $key->getKey());
        $this->assertEquals(1234567890, $key->getCreatedAt());
    }

    public function testGenerate(): void
    {
        $key = IdempotencyKey::generate();

        $this->assertEquals(32, strlen($key->getKey()));
        $this->assertMatchesRegularExpression('/^[a-f0-9]{32}$/', $key->getKey());
    }

    public function testGenerateCustomLength(): void
    {
        $key = IdempotencyKey::generate(16);
        $this->assertEquals(16, strlen($key->getKey()));

        $key = IdempotencyKey::generate(64);
        $this->assertEquals(64, strlen($key->getKey()));
    }

    public function testGenerateUniqueness(): void
    {
        $key1 = IdempotencyKey::generate();
        $key2 = IdempotencyKey::generate();

        $this->assertNotEquals($key1->getKey(), $key2->getKey());
    }

    public function testFromOperation(): void
    {
        $key = IdempotencyKey::fromOperation('purchase', [
            'amount' => 1000,
            'customer_id' => 'cus_123',
        ]);

        $this->assertEquals(32, strlen($key->getKey()));
        $this->assertMatchesRegularExpression('/^[a-f0-9]{32}$/', $key->getKey());
    }

    public function testFromOperationDeterministic(): void
    {
        $params = ['amount' => 1000, 'customer_id' => 'cus_123'];

        $key1 = IdempotencyKey::fromOperation('purchase', $params);
        $key2 = IdempotencyKey::fromOperation('purchase', $params);

        $this->assertEquals($key1->getKey(), $key2->getKey());
    }

    public function testFromOperationParamOrder(): void
    {
        // Different param order should produce same key (sorted internally)
        $key1 = IdempotencyKey::fromOperation('purchase', [
            'amount' => 1000,
            'customer_id' => 'cus_123',
        ]);

        $key2 = IdempotencyKey::fromOperation('purchase', [
            'customer_id' => 'cus_123',
            'amount' => 1000,
        ]);

        $this->assertEquals($key1->getKey(), $key2->getKey());
    }

    public function testFromOperationWithPrefix(): void
    {
        $key = IdempotencyKey::fromOperation('purchase', ['amount' => 1000], 'pur');

        $this->assertStringStartsWith('pur_', $key->getKey());
    }

    public function testFromOperationDifferentOps(): void
    {
        $params = ['amount' => 1000];
        $key1 = IdempotencyKey::fromOperation('purchase', $params);
        $key2 = IdempotencyKey::fromOperation('refund', $params);

        $this->assertNotEquals($key1->getKey(), $key2->getKey());
    }

    public function testForPurchase(): void
    {
        $key = IdempotencyKey::forPurchase(1000, 'cus_123', 'USD', 'pm_123');

        $this->assertStringStartsWith('pur_', $key->getKey());
        $this->assertNotEmpty($key->getKey());
    }

    public function testForRefund(): void
    {
        $key = IdempotencyKey::forRefund('pi_123', 500);

        $this->assertStringStartsWith('ref_', $key->getKey());
        $this->assertNotEmpty($key->getKey());
    }

    public function testFromString(): void
    {
        $key = IdempotencyKey::fromString('custom_key_12345678');

        $this->assertEquals('custom_key_12345678', $key->getKey());
    }

    public function testIsExpired(): void
    {
        // Fresh key - not expired
        $key = new IdempotencyKey('fresh_key');
        $this->assertFalse($key->isExpired());

        // Old key - expired (25 hours ago)
        $key = new IdempotencyKey('old_key', time() - 90000);
        $this->assertTrue($key->isExpired());

        // Just within limit (23 hours ago)
        $key = new IdempotencyKey('almost_key', time() - 82800);
        $this->assertFalse($key->isExpired());

        // Null createdAt - never expires
        $key = new IdempotencyKey('null_key');
        // Constructor sets createdAt to time() if null, so it won't be null
        $this->assertFalse($key->isExpired());
    }

    public function testGetRemainingTime(): void
    {
        // Fresh key - should have ~24 hours remaining
        $key = new IdempotencyKey('fresh_key', time());
        $remaining = $key->getRemainingTime();
        $this->assertGreaterThan(86300, $remaining);
        $this->assertLessThanOrEqual(86400, $remaining);

        // Expired key - 0 remaining
        $key = new IdempotencyKey('old_key', time() - 90000);
        $this->assertEquals(0, $key->getRemainingTime());

        // Half-expired key
        $key = new IdempotencyKey('half_key', time() - 43200);
        $remaining = $key->getRemainingTime();
        $this->assertGreaterThan(43100, $remaining);
        $this->assertLessThanOrEqual(43200, $remaining);
    }

    public function testToString(): void
    {
        $key = new IdempotencyKey('string_key_123');

        $this->assertEquals('string_key_123', (string) $key);
    }

    public function testIsValidFormat(): void
    {
        // Valid keys
        $this->assertTrue(IdempotencyKey::isValidFormat('abcdefgh'));
        $this->assertTrue(IdempotencyKey::isValidFormat('key_12345678'));
        $this->assertTrue(IdempotencyKey::isValidFormat('pur_abc123def456'));
        $this->assertTrue(IdempotencyKey::isValidFormat('a1b2c3d4e5f6g7h8'));
        $this->assertTrue(IdempotencyKey::isValidFormat('key-with-dashes'));

        // Invalid keys
        $this->assertFalse(IdempotencyKey::isValidFormat('short')); // too short
        $this->assertFalse(IdempotencyKey::isValidFormat('')); // empty
        $this->assertFalse(IdempotencyKey::isValidFormat('key with spaces')); // spaces
        $this->assertFalse(IdempotencyKey::isValidFormat('key.with.dots')); // dots
        $this->assertFalse(IdempotencyKey::isValidFormat(str_repeat('a', 65))); // too long
    }

    public function testMaxAgeConstant(): void
    {
        $this->assertEquals(86400, IdempotencyKey::MAX_AGE_SECONDS);
    }
}
