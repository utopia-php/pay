<?php

namespace Utopia\Pay\Payment;

/**
 * Payment class for managing payment/transaction data.
 *
 * Represents a payment intent or transaction in the payment system.
 */
class Payment
{
    /**
     * Payment requires payment method.
     */
    public const STATUS_REQUIRES_PAYMENT_METHOD = 'requires_payment_method';

    /**
     * Payment requires confirmation.
     */
    public const STATUS_REQUIRES_CONFIRMATION = 'requires_confirmation';

    /**
     * Payment requires action (e.g., 3D Secure authentication).
     */
    public const STATUS_REQUIRES_ACTION = 'requires_action';

    /**
     * Payment is processing.
     */
    public const STATUS_PROCESSING = 'processing';

    /**
     * Payment requires capture.
     */
    public const STATUS_REQUIRES_CAPTURE = 'requires_capture';

    /**
     * Payment was cancelled.
     */
    public const STATUS_CANCELLED = 'canceled';

    /**
     * Payment succeeded.
     */
    public const STATUS_SUCCEEDED = 'succeeded';

    /**
     * Create a new Payment instance.
     *
     * @param  string  $id  Unique identifier for the payment
     * @param  int  $amount  Payment amount in smallest currency unit (e.g., cents)
     * @param  string  $currency  Three-letter ISO currency code
     * @param  string  $status  Payment status
     * @param  string|null  $customerId  The customer ID associated with this payment
     * @param  string|null  $paymentMethodId  The payment method ID used for this payment
     * @param  string|null  $description  Description of the payment
     * @param  int|null  $amountReceived  Amount received (for partial captures)
     * @param  int|null  $amountRefunded  Amount refunded
     * @param  string|null  $clientSecret  Client secret for client-side confirmation
     * @param  string|null  $chargeId  The charge ID (if payment has been charged)
     * @param  string|null  $receiptEmail  Email to send receipt to
     * @param  string|null  $receiptUrl  URL to view receipt
     * @param  string|null  $failureCode  Error code if payment failed
     * @param  string|null  $failureMessage  Error message if payment failed
     * @param  array<string, mixed>  $metadata  Additional metadata
     * @param  int|null  $createdAt  Unix timestamp when payment was created
     */
    public function __construct(
        private string $id,
        private int $amount,
        private string $currency,
        private string $status = self::STATUS_REQUIRES_PAYMENT_METHOD,
        private ?string $customerId = null,
        private ?string $paymentMethodId = null,
        private ?string $description = null,
        private ?int $amountReceived = null,
        private ?int $amountRefunded = null,
        private ?string $clientSecret = null,
        private ?string $chargeId = null,
        private ?string $receiptEmail = null,
        private ?string $receiptUrl = null,
        private ?string $failureCode = null,
        private ?string $failureMessage = null,
        private array $metadata = [],
        private ?int $createdAt = null
    ) {
        $this->createdAt = $createdAt ?? time();
    }

    /**
     * Get the payment ID.
     *
     * @return string The unique payment identifier
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Set the payment ID.
     *
     * @param  string  $id  The payment ID
     * @return static
     */
    public function setId(string $id): static
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the payment amount.
     *
     * @return int The amount in smallest currency unit
     */
    public function getAmount(): int
    {
        return $this->amount;
    }

    /**
     * Set the payment amount.
     *
     * @param  int  $amount  The amount in smallest currency unit
     * @return static
     */
    public function setAmount(int $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get the currency code.
     *
     * @return string Three-letter ISO currency code
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * Set the currency code.
     *
     * @param  string  $currency  Three-letter ISO currency code
     * @return static
     */
    public function setCurrency(string $currency): static
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * Get the payment status.
     *
     * @return string The payment status
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * Set the payment status.
     *
     * @param  string  $status  The payment status
     * @return static
     */
    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get the customer ID.
     *
     * @return string|null The customer ID
     */
    public function getCustomerId(): ?string
    {
        return $this->customerId;
    }

    /**
     * Set the customer ID.
     *
     * @param  string|null  $customerId  The customer ID
     * @return static
     */
    public function setCustomerId(?string $customerId): static
    {
        $this->customerId = $customerId;

        return $this;
    }

    /**
     * Get the payment method ID.
     *
     * @return string|null The payment method ID
     */
    public function getPaymentMethodId(): ?string
    {
        return $this->paymentMethodId;
    }

    /**
     * Set the payment method ID.
     *
     * @param  string|null  $paymentMethodId  The payment method ID
     * @return static
     */
    public function setPaymentMethodId(?string $paymentMethodId): static
    {
        $this->paymentMethodId = $paymentMethodId;

        return $this;
    }

    /**
     * Get the description.
     *
     * @return string|null The payment description
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Set the description.
     *
     * @param  string|null  $description  The payment description
     * @return static
     */
    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get the amount received.
     *
     * @return int|null The amount received
     */
    public function getAmountReceived(): ?int
    {
        return $this->amountReceived;
    }

    /**
     * Set the amount received.
     *
     * @param  int|null  $amountReceived  The amount received
     * @return static
     */
    public function setAmountReceived(?int $amountReceived): static
    {
        $this->amountReceived = $amountReceived;

        return $this;
    }

    /**
     * Get the amount refunded.
     *
     * @return int|null The amount refunded
     */
    public function getAmountRefunded(): ?int
    {
        return $this->amountRefunded;
    }

    /**
     * Set the amount refunded.
     *
     * @param  int|null  $amountRefunded  The amount refunded
     * @return static
     */
    public function setAmountRefunded(?int $amountRefunded): static
    {
        $this->amountRefunded = $amountRefunded;

        return $this;
    }

    /**
     * Get the client secret.
     *
     * @return string|null The client secret
     */
    public function getClientSecret(): ?string
    {
        return $this->clientSecret;
    }

    /**
     * Set the client secret.
     *
     * @param  string|null  $clientSecret  The client secret
     * @return static
     */
    public function setClientSecret(?string $clientSecret): static
    {
        $this->clientSecret = $clientSecret;

        return $this;
    }

    /**
     * Get the charge ID.
     *
     * @return string|null The charge ID
     */
    public function getChargeId(): ?string
    {
        return $this->chargeId;
    }

    /**
     * Set the charge ID.
     *
     * @param  string|null  $chargeId  The charge ID
     * @return static
     */
    public function setChargeId(?string $chargeId): static
    {
        $this->chargeId = $chargeId;

        return $this;
    }

    /**
     * Get the receipt email.
     *
     * @return string|null The receipt email
     */
    public function getReceiptEmail(): ?string
    {
        return $this->receiptEmail;
    }

    /**
     * Set the receipt email.
     *
     * @param  string|null  $receiptEmail  The receipt email
     * @return static
     */
    public function setReceiptEmail(?string $receiptEmail): static
    {
        $this->receiptEmail = $receiptEmail;

        return $this;
    }

    /**
     * Get the receipt URL.
     *
     * @return string|null The receipt URL
     */
    public function getReceiptUrl(): ?string
    {
        return $this->receiptUrl;
    }

    /**
     * Set the receipt URL.
     *
     * @param  string|null  $receiptUrl  The receipt URL
     * @return static
     */
    public function setReceiptUrl(?string $receiptUrl): static
    {
        $this->receiptUrl = $receiptUrl;

        return $this;
    }

    /**
     * Get the failure code.
     *
     * @return string|null The failure code
     */
    public function getFailureCode(): ?string
    {
        return $this->failureCode;
    }

    /**
     * Set the failure code.
     *
     * @param  string|null  $failureCode  The failure code
     * @return static
     */
    public function setFailureCode(?string $failureCode): static
    {
        $this->failureCode = $failureCode;

        return $this;
    }

    /**
     * Get the failure message.
     *
     * @return string|null The failure message
     */
    public function getFailureMessage(): ?string
    {
        return $this->failureMessage;
    }

    /**
     * Set the failure message.
     *
     * @param  string|null  $failureMessage  The failure message
     * @return static
     */
    public function setFailureMessage(?string $failureMessage): static
    {
        $this->failureMessage = $failureMessage;

        return $this;
    }

    /**
     * Get the metadata.
     *
     * @return array<string, mixed> The metadata
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    /**
     * Set the metadata.
     *
     * @param  array<string, mixed>  $metadata  The metadata
     * @return static
     */
    public function setMetadata(array $metadata): static
    {
        $this->metadata = $metadata;

        return $this;
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
     * Set the creation timestamp.
     *
     * @param  int|null  $createdAt  Unix timestamp
     * @return static
     */
    public function setCreatedAt(?int $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Check if payment succeeded.
     *
     * @return bool True if payment succeeded
     */
    public function isSucceeded(): bool
    {
        return $this->status === self::STATUS_SUCCEEDED;
    }

    /**
     * Check if payment is processing.
     *
     * @return bool True if payment is processing
     */
    public function isProcessing(): bool
    {
        return $this->status === self::STATUS_PROCESSING;
    }

    /**
     * Check if payment was cancelled.
     *
     * @return bool True if payment was cancelled
     */
    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /**
     * Check if payment requires action (e.g., 3D Secure).
     *
     * @return bool True if payment requires action
     */
    public function requiresAction(): bool
    {
        return $this->status === self::STATUS_REQUIRES_ACTION;
    }

    /**
     * Check if payment requires a payment method.
     *
     * @return bool True if payment requires payment method
     */
    public function requiresPaymentMethod(): bool
    {
        return $this->status === self::STATUS_REQUIRES_PAYMENT_METHOD;
    }

    /**
     * Check if payment failed.
     *
     * @return bool True if payment has failure info
     */
    public function hasFailed(): bool
    {
        return $this->failureCode !== null || $this->failureMessage !== null;
    }

    /**
     * Check if payment has been refunded (partially or fully).
     *
     * @return bool True if payment has been refunded
     */
    public function isRefunded(): bool
    {
        return $this->amountRefunded !== null && $this->amountRefunded > 0;
    }

    /**
     * Check if payment has been fully refunded.
     *
     * @return bool True if fully refunded
     */
    public function isFullyRefunded(): bool
    {
        return $this->amountRefunded !== null && $this->amountRefunded >= $this->amount;
    }

    /**
     * Get the net amount (amount - refunded).
     *
     * @return int The net amount
     */
    public function getNetAmount(): int
    {
        return $this->amount - ($this->amountRefunded ?? 0);
    }

    /**
     * Get the amount as a formatted decimal (for display).
     *
     * @param  int  $decimals  Number of decimal places (default: 2)
     * @return float The amount as a decimal
     */
    public function getAmountDecimal(int $decimals = 2): float
    {
        return round($this->amount / 100, $decimals);
    }

    /**
     * Convert the payment to an array representation.
     *
     * @return array<string, mixed> The payment data as an array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'status' => $this->status,
            'customerId' => $this->customerId,
            'paymentMethodId' => $this->paymentMethodId,
            'description' => $this->description,
            'amountReceived' => $this->amountReceived,
            'amountRefunded' => $this->amountRefunded,
            'clientSecret' => $this->clientSecret,
            'chargeId' => $this->chargeId,
            'receiptEmail' => $this->receiptEmail,
            'receiptUrl' => $this->receiptUrl,
            'failureCode' => $this->failureCode,
            'failureMessage' => $this->failureMessage,
            'metadata' => $this->metadata,
            'createdAt' => $this->createdAt,
        ];
    }

    /**
     * Create a Payment instance from an array.
     *
     * @param  array<string, mixed>  $data  The payment data array
     * @return self The created Payment instance
     */
    public static function fromArray(array $data): self
    {
        // Handle Stripe's nested structure
        $latestCharge = $data['latest_charge'] ?? null;
        $chargeId = null;
        $receiptUrl = null;
        $failureCode = null;
        $failureMessage = null;

        if (is_array($latestCharge)) {
            $chargeId = $latestCharge['id'] ?? null;
            $receiptUrl = $latestCharge['receipt_url'] ?? null;
            $failureCode = $latestCharge['failure_code'] ?? null;
            $failureMessage = $latestCharge['failure_message'] ?? null;
        } elseif (is_string($latestCharge)) {
            $chargeId = $latestCharge;
        }

        return new self(
            id: $data['id'] ?? $data['$id'] ?? uniqid('pi_'),
            amount: (int) ($data['amount'] ?? 0),
            currency: strtoupper($data['currency'] ?? 'USD'),
            status: $data['status'] ?? self::STATUS_REQUIRES_PAYMENT_METHOD,
            customerId: $data['customerId'] ?? $data['customer'] ?? null,
            paymentMethodId: $data['paymentMethodId'] ?? $data['payment_method'] ?? null,
            description: $data['description'] ?? null,
            amountReceived: isset($data['amount_received']) ? (int) $data['amount_received'] : ($data['amountReceived'] ?? null),
            amountRefunded: isset($data['amount_refunded']) ? (int) $data['amount_refunded'] : ($data['amountRefunded'] ?? null),
            clientSecret: $data['clientSecret'] ?? $data['client_secret'] ?? null,
            chargeId: $data['chargeId'] ?? $chargeId,
            receiptEmail: $data['receiptEmail'] ?? $data['receipt_email'] ?? null,
            receiptUrl: $data['receiptUrl'] ?? $receiptUrl,
            failureCode: $data['failureCode'] ?? $failureCode ?? ($data['last_payment_error']['code'] ?? null),
            failureMessage: $data['failureMessage'] ?? $failureMessage ?? ($data['last_payment_error']['message'] ?? null),
            metadata: $data['metadata'] ?? [],
            createdAt: $data['createdAt'] ?? $data['created'] ?? null
        );
    }
}
