<?php

namespace Utopia\Pay\SetupIntent;

use Utopia\Pay\Expandable;

/**
 * Typed view of a setup intent as returned by the adapter, e.g. SetupIntent::fromArray($pay->getFuturePayment($id)).
 */
class SetupIntent
{
    use Expandable;

    public const STATUS_SUCCEEDED = 'succeeded';

    public function __construct(
        private string $id,
        private string $status,
        private ?string $customerId = null,
        private ?string $paymentMethodId = null,
        private ?string $clientSecret = null,
        private ?string $mandateId = null,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
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

    public function getClientSecret(): ?string
    {
        return $this->clientSecret;
    }

    public function getMandateId(): ?string
    {
        return $this->mandateId;
    }

    public function isSucceeded(): bool
    {
        return $this->status === self::STATUS_SUCCEEDED;
    }

    /**
     * @param  array<string, mixed>  $data  Setup intent payload
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: (string) ($data['id'] ?? ''),
            status: (string) ($data['status'] ?? ''),
            customerId: self::expandableId($data['customer'] ?? null),
            paymentMethodId: self::expandableId($data['payment_method'] ?? null),
            clientSecret: $data['client_secret'] ?? null,
            mandateId: self::expandableId($data['mandate'] ?? null),
        );
    }
}
