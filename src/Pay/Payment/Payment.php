<?php

namespace Utopia\Pay\Payment;

use Utopia\Pay\Expandable;

/**
 * Typed view of a payment intent as returned by the adapter, e.g. Payment::fromArray($pay->getPayment($id)).
 */
class Payment
{
    use Expandable;

    public const STATUS_REQUIRES_PAYMENT_METHOD = 'requires_payment_method';

    public const STATUS_REQUIRES_CONFIRMATION = 'requires_confirmation';

    public const STATUS_REQUIRES_ACTION = 'requires_action';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_REQUIRES_CAPTURE = 'requires_capture';

    public const STATUS_CANCELED = 'canceled';

    public const STATUS_SUCCEEDED = 'succeeded';

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        private string $id,
        private int $amount,
        private string $currency,
        private string $status,
        private ?string $customerId = null,
        private ?string $paymentMethodId = null,
        private int $amountReceived = 0,
        private ?int $amountRefunded = null,
        private ?string $clientSecret = null,
        private ?string $chargeId = null,
        private ?string $errorCode = null,
        private ?string $errorMessage = null,
        private array $metadata = [],
        private ?int $createdAt = null,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Amount in the smallest currency unit
     */
    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getCustomerId(): ?string
    {
        return $this->customerId;
    }

    public function getPaymentMethodId(): ?string
    {
        return $this->paymentMethodId;
    }

    public function getAmountReceived(): int
    {
        return $this->amountReceived;
    }

    /**
     * Null when the payload carries no charge data to sum refunds from
     */
    public function getAmountRefunded(): ?int
    {
        return $this->amountRefunded;
    }

    public function getClientSecret(): ?string
    {
        return $this->clientSecret;
    }

    public function getChargeId(): ?string
    {
        return $this->chargeId;
    }

    /**
     * Decline or error code of the last failed attempt, matching Exception::getType()
     */
    public function getErrorCode(): ?string
    {
        return $this->errorCode;
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    /**
     * @return array<string, mixed>
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function getCreatedAt(): ?int
    {
        return $this->createdAt;
    }

    public function isSucceeded(): bool
    {
        return $this->status === self::STATUS_SUCCEEDED;
    }

    public function isProcessing(): bool
    {
        return $this->status === self::STATUS_PROCESSING;
    }

    public function isCanceled(): bool
    {
        return $this->status === self::STATUS_CANCELED;
    }

    public function requiresAction(): bool
    {
        return $this->status === self::STATUS_REQUIRES_ACTION;
    }

    public function requiresCapture(): bool
    {
        return $this->status === self::STATUS_REQUIRES_CAPTURE;
    }

    public function requiresPaymentMethod(): bool
    {
        return $this->status === self::STATUS_REQUIRES_PAYMENT_METHOD;
    }

    /**
     * @param  array<string, mixed>  $data  Payment intent payload
     */
    public static function fromArray(array $data): self
    {
        $error = $data['last_payment_error'] ?? [];

        // Refunds live on charges: the legacy `charges` list, or an expanded `latest_charge`
        $amountRefunded = null;
        $charges = $data['charges']['data'] ?? null;
        if (is_array($charges)) {
            $amountRefunded = array_sum(array_map(fn ($charge) => (int) ($charge['amount_refunded'] ?? 0), $charges));
        } elseif (is_array($data['latest_charge'] ?? null)) {
            $amountRefunded = (int) ($data['latest_charge']['amount_refunded'] ?? 0);
        }

        return new self(
            id: (string) ($data['id'] ?? ''),
            amount: (int) ($data['amount'] ?? 0),
            currency: (string) ($data['currency'] ?? ''),
            status: (string) ($data['status'] ?? ''),
            customerId: self::expandableId($data['customer'] ?? null),
            paymentMethodId: self::expandableId($data['payment_method'] ?? null),
            amountReceived: (int) ($data['amount_received'] ?? 0),
            amountRefunded: $amountRefunded,
            clientSecret: $data['client_secret'] ?? null,
            chargeId: self::expandableId($data['latest_charge'] ?? null),
            // Same precedence as Stripe::handleError() so both sides compare against Exception constants
            errorCode: $error['decline_code'] ?? $error['code'] ?? null,
            errorMessage: $error['message'] ?? null,
            metadata: $data['metadata'] ?? [],
            createdAt: isset($data['created']) ? (int) $data['created'] : null,
        );
    }
}
