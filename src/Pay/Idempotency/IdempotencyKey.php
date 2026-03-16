<?php

namespace Utopia\Pay\Idempotency;

/**
 * IdempotencyKey class for preventing duplicate operations.
 *
 * Idempotency keys ensure that a request is only processed once,
 * preventing duplicate charges or operations when retrying failed requests.
 */
class IdempotencyKey
{
    /**
     * Default key length for generated keys.
     */
    private const DEFAULT_KEY_LENGTH = 32;

    /**
     * Maximum age for idempotency keys (24 hours in seconds).
     * Most payment providers expire keys after 24 hours.
     */
    public const MAX_AGE_SECONDS = 86400;

    /**
     * Create a new IdempotencyKey instance.
     *
     * @param  string  $key  The idempotency key value
     * @param  int|null  $createdAt  Unix timestamp when key was created
     */
    public function __construct(
        private string $key,
        private ?int $createdAt = null
    ) {
        $this->createdAt = $createdAt ?? time();
    }

    /**
     * Get the key value.
     *
     * @return string The idempotency key
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * Get the creation timestamp.
     *
     * @return int|null Unix timestamp
     */
    public function getCreatedAt(): ?int
    {
        return $this->createdAt;
    }

    /**
     * Check if the key has expired.
     *
     * @return bool True if expired
     */
    public function isExpired(): bool
    {
        if ($this->createdAt === null) {
            return false;
        }

        return (time() - $this->createdAt) > self::MAX_AGE_SECONDS;
    }

    /**
     * Get remaining validity time in seconds.
     *
     * @return int Seconds remaining, 0 if expired
     */
    public function getRemainingTime(): int
    {
        if ($this->createdAt === null) {
            return self::MAX_AGE_SECONDS;
        }

        $elapsed = time() - $this->createdAt;
        $remaining = self::MAX_AGE_SECONDS - $elapsed;

        return max(0, $remaining);
    }

    /**
     * Get the key as a string.
     *
     * @return string The idempotency key
     */
    public function __toString(): string
    {
        return $this->key;
    }

    /**
     * Generate a new random idempotency key.
     *
     * @param  int  $length  The length of the key (default: 32)
     * @return self A new IdempotencyKey instance
     */
    public static function generate(int $length = self::DEFAULT_KEY_LENGTH): self
    {
        $bytes = random_bytes((int) ceil($length / 2));
        $key = substr(bin2hex($bytes), 0, $length);

        return new self($key);
    }

    /**
     * Generate an idempotency key based on operation parameters.
     *
     * This creates a deterministic key based on the operation details,
     * ensuring the same operation always produces the same key.
     *
     * @param  string  $operation  The operation type (e.g., 'purchase', 'refund')
     * @param  array<string, mixed>  $params  The operation parameters
     * @param  string|null  $prefix  Optional prefix for the key
     * @return self A new IdempotencyKey instance
     */
    public static function fromOperation(string $operation, array $params, ?string $prefix = null): self
    {
        // Sort params for consistent hashing
        ksort($params);

        // Create a hash of the operation and params
        $data = $operation.':'.json_encode($params);
        $hash = hash('sha256', $data);

        // Take first 32 characters of the hash
        $key = substr($hash, 0, 32);

        if ($prefix !== null) {
            $key = $prefix.'_'.$key;
        }

        return new self($key);
    }

    /**
     * Create an idempotency key for a purchase operation.
     *
     * @param  int  $amount  The purchase amount
     * @param  string  $customerId  The customer ID
     * @param  string  $currency  The currency code
     * @param  string|null  $paymentMethodId  The payment method ID
     * @return self A new IdempotencyKey instance
     */
    public static function forPurchase(int $amount, string $customerId, string $currency, ?string $paymentMethodId = null): self
    {
        return self::fromOperation('purchase', [
            'amount' => $amount,
            'customer_id' => $customerId,
            'currency' => $currency,
            'payment_method_id' => $paymentMethodId,
            'timestamp' => date('Y-m-d-H'), // Hour-level granularity
        ], 'pur');
    }

    /**
     * Create an idempotency key for a refund operation.
     *
     * @param  string  $paymentId  The payment ID to refund
     * @param  int|null  $amount  The refund amount
     * @return self A new IdempotencyKey instance
     */
    public static function forRefund(string $paymentId, ?int $amount = null): self
    {
        return self::fromOperation('refund', [
            'payment_id' => $paymentId,
            'amount' => $amount,
            'timestamp' => date('Y-m-d-H'),
        ], 'ref');
    }

    /**
     * Create an idempotency key from an existing string.
     *
     * @param  string  $key  The key string
     * @return self A new IdempotencyKey instance
     */
    public static function fromString(string $key): self
    {
        return new self($key);
    }

    /**
     * Validate an idempotency key format.
     *
     * @param  string  $key  The key to validate
     * @return bool True if valid format
     */
    public static function isValidFormat(string $key): bool
    {
        // Key should be alphanumeric with optional underscores, 8-64 characters
        return (bool) preg_match('/^[a-zA-Z0-9_-]{8,64}$/', $key);
    }
}
