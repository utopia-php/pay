<?php

namespace Utopia\Pay\SetupIntent;

/**
 * SetupIntent class for managing future payment setups.
 *
 * Represents a setup intent for collecting payment method details
 * for future payments without charging immediately.
 */
class SetupIntent
{
    /**
     * Setup requires a payment method.
     */
    public const STATUS_REQUIRES_PAYMENT_METHOD = 'requires_payment_method';

    /**
     * Setup requires confirmation.
     */
    public const STATUS_REQUIRES_CONFIRMATION = 'requires_confirmation';

    /**
     * Setup requires action (e.g., 3D Secure).
     */
    public const STATUS_REQUIRES_ACTION = 'requires_action';

    /**
     * Setup is processing.
     */
    public const STATUS_PROCESSING = 'processing';

    /**
     * Setup was canceled.
     */
    public const STATUS_CANCELED = 'canceled';

    /**
     * Setup succeeded.
     */
    public const STATUS_SUCCEEDED = 'succeeded';

    /**
     * Usage: On-session (customer is present).
     */
    public const USAGE_ON_SESSION = 'on_session';

    /**
     * Usage: Off-session (customer not present).
     */
    public const USAGE_OFF_SESSION = 'off_session';

    /**
     * Cancellation reason: Abandoned by customer.
     */
    public const CANCELLATION_ABANDONED = 'abandoned';

    /**
     * Cancellation reason: Requested by customer.
     */
    public const CANCELLATION_REQUESTED_BY_CUSTOMER = 'requested_by_customer';

    /**
     * Cancellation reason: Duplicate setup.
     */
    public const CANCELLATION_DUPLICATE = 'duplicate';

    /**
     * Create a new SetupIntent instance.
     *
     * @param  string  $id  Unique identifier for the setup intent
     * @param  string  $status  Setup intent status
     * @param  string|null  $customerId  Customer ID this setup is for
     * @param  string|null  $paymentMethodId  Payment method ID attached
     * @param  string|null  $clientSecret  Client secret for frontend confirmation
     * @param  string  $usage  Intended usage (on_session or off_session)
     * @param  string|null  $description  Description of the setup
     * @param  string|null  $mandateId  Mandate ID if created
     * @param  array<string>  $paymentMethodTypes  Allowed payment method types
     * @param  string|null  $cancellationReason  Reason for cancellation if canceled
     * @param  array<string, mixed>  $lastSetupError  Last error if setup failed
     * @param  array<string, mixed>  $nextAction  Next action required
     * @param  array<string, mixed>  $metadata  Additional metadata
     * @param  int|null  $createdAt  Unix timestamp when created
     */
    public function __construct(
        private string $id,
        private string $status = self::STATUS_REQUIRES_PAYMENT_METHOD,
        private ?string $customerId = null,
        private ?string $paymentMethodId = null,
        private ?string $clientSecret = null,
        private string $usage = self::USAGE_OFF_SESSION,
        private ?string $description = null,
        private ?string $mandateId = null,
        private array $paymentMethodTypes = ['card'],
        private ?string $cancellationReason = null,
        private array $lastSetupError = [],
        private array $nextAction = [],
        private array $metadata = [],
        private ?int $createdAt = null
    ) {
        $this->createdAt = $createdAt ?? time();
    }

    /**
     * Get the setup intent ID.
     *
     * @return string The unique identifier
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Set the setup intent ID.
     *
     * @param  string  $id  The setup intent ID
     * @return static
     */
    public function setId(string $id): static
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the status.
     *
     * @return string The setup intent status
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * Set the status.
     *
     * @param  string  $status  The status
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
     * Get the client secret.
     *
     * @return string|null The client secret for frontend use
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
     * Get the intended usage.
     *
     * @return string The usage (on_session or off_session)
     */
    public function getUsage(): string
    {
        return $this->usage;
    }

    /**
     * Set the intended usage.
     *
     * @param  string  $usage  The usage
     * @return static
     */
    public function setUsage(string $usage): static
    {
        $this->usage = $usage;

        return $this;
    }

    /**
     * Get the description.
     *
     * @return string|null The description
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Set the description.
     *
     * @param  string|null  $description  The description
     * @return static
     */
    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get the mandate ID.
     *
     * @return string|null The mandate ID
     */
    public function getMandateId(): ?string
    {
        return $this->mandateId;
    }

    /**
     * Set the mandate ID.
     *
     * @param  string|null  $mandateId  The mandate ID
     * @return static
     */
    public function setMandateId(?string $mandateId): static
    {
        $this->mandateId = $mandateId;

        return $this;
    }

    /**
     * Get allowed payment method types.
     *
     * @return array<string> The payment method types
     */
    public function getPaymentMethodTypes(): array
    {
        return $this->paymentMethodTypes;
    }

    /**
     * Set allowed payment method types.
     *
     * @param  array<string>  $paymentMethodTypes  The payment method types
     * @return static
     */
    public function setPaymentMethodTypes(array $paymentMethodTypes): static
    {
        $this->paymentMethodTypes = $paymentMethodTypes;

        return $this;
    }

    /**
     * Get the cancellation reason.
     *
     * @return string|null The cancellation reason
     */
    public function getCancellationReason(): ?string
    {
        return $this->cancellationReason;
    }

    /**
     * Set the cancellation reason.
     *
     * @param  string|null  $cancellationReason  The cancellation reason
     * @return static
     */
    public function setCancellationReason(?string $cancellationReason): static
    {
        $this->cancellationReason = $cancellationReason;

        return $this;
    }

    /**
     * Get the last setup error.
     *
     * @return array<string, mixed> The error details
     */
    public function getLastSetupError(): array
    {
        return $this->lastSetupError;
    }

    /**
     * Set the last setup error.
     *
     * @param  array<string, mixed>  $lastSetupError  The error details
     * @return static
     */
    public function setLastSetupError(array $lastSetupError): static
    {
        $this->lastSetupError = $lastSetupError;

        return $this;
    }

    /**
     * Get the next action required.
     *
     * @return array<string, mixed> The next action details
     */
    public function getNextAction(): array
    {
        return $this->nextAction;
    }

    /**
     * Set the next action.
     *
     * @param  array<string, mixed>  $nextAction  The next action details
     * @return static
     */
    public function setNextAction(array $nextAction): static
    {
        $this->nextAction = $nextAction;

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
     * Check if setup succeeded.
     *
     * @return bool True if setup was successful
     */
    public function isSucceeded(): bool
    {
        return $this->status === self::STATUS_SUCCEEDED;
    }

    /**
     * Check if setup was canceled.
     *
     * @return bool True if canceled
     */
    public function isCanceled(): bool
    {
        return $this->status === self::STATUS_CANCELED;
    }

    /**
     * Check if setup requires action.
     *
     * @return bool True if action is required
     */
    public function requiresAction(): bool
    {
        return $this->status === self::STATUS_REQUIRES_ACTION;
    }

    /**
     * Check if setup requires payment method.
     *
     * @return bool True if payment method is required
     */
    public function requiresPaymentMethod(): bool
    {
        return $this->status === self::STATUS_REQUIRES_PAYMENT_METHOD;
    }

    /**
     * Check if setup requires confirmation.
     *
     * @return bool True if confirmation is required
     */
    public function requiresConfirmation(): bool
    {
        return $this->status === self::STATUS_REQUIRES_CONFIRMATION;
    }

    /**
     * Check if setup is processing.
     *
     * @return bool True if processing
     */
    public function isProcessing(): bool
    {
        return $this->status === self::STATUS_PROCESSING;
    }

    /**
     * Check if setup is complete (succeeded or canceled).
     *
     * @return bool True if complete
     */
    public function isComplete(): bool
    {
        return in_array($this->status, [
            self::STATUS_SUCCEEDED,
            self::STATUS_CANCELED,
        ]);
    }

    /**
     * Check if setup is for off-session usage.
     *
     * @return bool True if for off-session
     */
    public function isOffSession(): bool
    {
        return $this->usage === self::USAGE_OFF_SESSION;
    }

    /**
     * Check if there was a setup error.
     *
     * @return bool True if there was an error
     */
    public function hasError(): bool
    {
        return ! empty($this->lastSetupError);
    }

    /**
     * Get the error message if any.
     *
     * @return string|null The error message
     */
    public function getErrorMessage(): ?string
    {
        return $this->lastSetupError['message'] ?? null;
    }

    /**
     * Get the error code if any.
     *
     * @return string|null The error code
     */
    public function getErrorCode(): ?string
    {
        return $this->lastSetupError['code'] ?? null;
    }

    /**
     * Check if a mandate was created.
     *
     * @return bool True if mandate exists
     */
    public function hasMandate(): bool
    {
        return $this->mandateId !== null;
    }

    /**
     * Check if a payment method is attached.
     *
     * @return bool True if payment method is attached
     */
    public function hasPaymentMethod(): bool
    {
        return $this->paymentMethodId !== null;
    }

    /**
     * Convert the setup intent to an array representation.
     *
     * @return array<string, mixed> The setup intent data as an array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'customerId' => $this->customerId,
            'paymentMethodId' => $this->paymentMethodId,
            'clientSecret' => $this->clientSecret,
            'usage' => $this->usage,
            'description' => $this->description,
            'mandateId' => $this->mandateId,
            'paymentMethodTypes' => $this->paymentMethodTypes,
            'cancellationReason' => $this->cancellationReason,
            'lastSetupError' => $this->lastSetupError,
            'nextAction' => $this->nextAction,
            'metadata' => $this->metadata,
            'createdAt' => $this->createdAt,
        ];
    }

    /**
     * Create a SetupIntent instance from an array.
     *
     * @param  array<string, mixed>  $data  The setup intent data array
     * @return self The created SetupIntent instance
     */
    public static function fromArray(array $data): self
    {
        // Handle customer as string or object
        $customerId = $data['customerId'] ?? $data['customer'] ?? null;
        if (is_array($customerId)) {
            $customerId = $customerId['id'] ?? null;
        }

        // Handle payment method as string or object
        $paymentMethodId = $data['paymentMethodId'] ?? $data['payment_method'] ?? null;
        if (is_array($paymentMethodId)) {
            $paymentMethodId = $paymentMethodId['id'] ?? null;
        }

        return new self(
            id: $data['id'] ?? $data['$id'] ?? uniqid('seti_'),
            status: $data['status'] ?? self::STATUS_REQUIRES_PAYMENT_METHOD,
            customerId: $customerId,
            paymentMethodId: $paymentMethodId,
            clientSecret: $data['clientSecret'] ?? $data['client_secret'] ?? null,
            usage: $data['usage'] ?? self::USAGE_OFF_SESSION,
            description: $data['description'] ?? null,
            mandateId: $data['mandateId'] ?? $data['mandate'] ?? null,
            paymentMethodTypes: $data['paymentMethodTypes'] ?? $data['payment_method_types'] ?? ['card'],
            cancellationReason: $data['cancellationReason'] ?? $data['cancellation_reason'] ?? null,
            lastSetupError: $data['lastSetupError'] ?? $data['last_setup_error'] ?? [],
            nextAction: $data['nextAction'] ?? $data['next_action'] ?? [],
            metadata: $data['metadata'] ?? [],
            createdAt: $data['createdAt'] ?? $data['created'] ?? null
        );
    }
}
