<?php

namespace Utopia\Pay\Webhook;

/**
 * WebhookEvent class for handling payment webhook events.
 *
 * A provider-agnostic class for processing webhook notifications
 * from any payment provider. Provider-specific event constants
 * should be defined in provider-specific classes.
 *
 * @see \Utopia\Pay\Adapter\Stripe\StripeWebhookEvents for Stripe-specific events
 */
class WebhookEvent
{
    // Generic event categories (provider-agnostic)
    public const CATEGORY_PAYMENT = 'payment';

    public const CATEGORY_REFUND = 'refund';

    public const CATEGORY_CUSTOMER = 'customer';

    public const CATEGORY_PAYMENT_METHOD = 'payment_method';

    public const CATEGORY_DISPUTE = 'dispute';

    public const CATEGORY_SUBSCRIPTION = 'subscription';

    public const CATEGORY_INVOICE = 'invoice';

    public const CATEGORY_PAYOUT = 'payout';

    public const CATEGORY_SETUP = 'setup';

    // Generic event actions (provider-agnostic)
    public const ACTION_CREATED = 'created';

    public const ACTION_UPDATED = 'updated';

    public const ACTION_DELETED = 'deleted';

    public const ACTION_SUCCEEDED = 'succeeded';

    public const ACTION_FAILED = 'failed';

    public const ACTION_CANCELED = 'canceled';

    public const ACTION_PENDING = 'pending';

    public const ACTION_REQUIRES_ACTION = 'requires_action';

    public const ACTION_REFUNDED = 'refunded';

    public const ACTION_CAPTURED = 'captured';

    /**
     * Create a new WebhookEvent instance.
     *
     * @param  string  $id  Unique identifier for the event
     * @param  string  $type  Event type (provider-specific format)
     * @param  array<string, mixed>  $data  Event data/payload
     * @param  string|null  $provider  Payment provider name
     * @param  string|null  $apiVersion  API version used
     * @param  bool  $livemode  Whether this is a live event
     * @param  int|null  $createdAt  Unix timestamp when event was created
     * @param  int  $pendingWebhooks  Number of pending webhook deliveries
     * @param  string|null  $requestId  Request ID if available
     */
    public function __construct(
        private string $id,
        private string $type,
        private array $data = [],
        private ?string $provider = null,
        private ?string $apiVersion = null,
        private bool $livemode = false,
        private ?int $createdAt = null,
        private int $pendingWebhooks = 0,
        private ?string $requestId = null
    ) {
        $this->createdAt = $createdAt ?? time();
    }

    /**
     * Get the event ID.
     *
     * @return string The unique event identifier
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Get the event type.
     *
     * @return string The event type (provider-specific format)
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Get the payment provider name.
     *
     * @return string|null The provider name
     */
    public function getProvider(): ?string
    {
        return $this->provider;
    }

    /**
     * Get the event data/payload.
     *
     * @return array<string, mixed> The event data
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Get the data object from the event.
     *
     * @return array<string, mixed> The data object
     */
    public function getObject(): array
    {
        return $this->data['object'] ?? $this->data;
    }

    /**
     * Get the API version.
     *
     * @return string|null The API version
     */
    public function getApiVersion(): ?string
    {
        return $this->apiVersion;
    }

    /**
     * Check if this is a live mode event.
     *
     * @return bool True if live mode
     */
    public function isLivemode(): bool
    {
        return $this->livemode;
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
     * Get the number of pending webhooks.
     *
     * @return int Number of pending deliveries
     */
    public function getPendingWebhooks(): int
    {
        return $this->pendingWebhooks;
    }

    /**
     * Get the request ID.
     *
     * @return string|null The request ID
     */
    public function getRequestId(): ?string
    {
        return $this->requestId;
    }

    /**
     * Check if event type contains a specific keyword.
     *
     * @param  string  $keyword  The keyword to check for
     * @return bool True if event type contains the keyword
     */
    public function typeContains(string $keyword): bool
    {
        return str_contains(strtolower($this->type), strtolower($keyword));
    }

    /**
     * Check if this is a payment-related event.
     *
     * @return bool True if payment-related event
     */
    public function isPaymentEvent(): bool
    {
        return $this->typeContains('payment') ||
               $this->typeContains('charge') ||
               $this->typeContains('transaction');
    }

    /**
     * Check if this is a customer event.
     *
     * @return bool True if customer-related event
     */
    public function isCustomerEvent(): bool
    {
        return $this->typeContains('customer');
    }

    /**
     * Check if this is a subscription event.
     *
     * @return bool True if subscription-related event
     */
    public function isSubscriptionEvent(): bool
    {
        return $this->typeContains('subscription');
    }

    /**
     * Check if this is a dispute event.
     *
     * @return bool True if dispute-related event
     */
    public function isDisputeEvent(): bool
    {
        return $this->typeContains('dispute') ||
               $this->typeContains('chargeback');
    }

    /**
     * Check if this is a refund event.
     *
     * @return bool True if refund-related event
     */
    public function isRefundEvent(): bool
    {
        return $this->typeContains('refund');
    }

    /**
     * Check if this is an invoice event.
     *
     * @return bool True if invoice-related event
     */
    public function isInvoiceEvent(): bool
    {
        return $this->typeContains('invoice');
    }

    /**
     * Check if this is a setup/mandate event.
     *
     * @return bool True if setup-related event
     */
    public function isSetupEvent(): bool
    {
        return $this->typeContains('setup') ||
               $this->typeContains('mandate');
    }

    /**
     * Check if this is a payment method event.
     *
     * @return bool True if payment method-related event
     */
    public function isPaymentMethodEvent(): bool
    {
        return $this->typeContains('payment_method') ||
               $this->typeContains('card') ||
               $this->typeContains('source');
    }

    /**
     * Check if this event indicates a successful action.
     *
     * @return bool True if success event
     */
    public function isSuccessEvent(): bool
    {
        return $this->typeContains('succeeded') ||
               $this->typeContains('success') ||
               $this->typeContains('paid') ||
               $this->typeContains('captured') ||
               $this->typeContains('completed');
    }

    /**
     * Check if this event indicates a failure.
     *
     * @return bool True if failure event
     */
    public function isFailureEvent(): bool
    {
        return $this->typeContains('failed') ||
               $this->typeContains('failure') ||
               $this->typeContains('declined');
    }

    /**
     * Check if this event requires immediate action.
     *
     * @return bool True if action required
     */
    public function requiresAction(): bool
    {
        return $this->typeContains('requires_action') ||
               $this->typeContains('action_required') ||
               $this->typeContains('pending') ||
               ($this->isDisputeEvent() && $this->typeContains('created'));
    }

    /**
     * Get the action from the event type.
     *
     * This extracts the last part of a dot-separated event type.
     * For example, 'payment_intent.succeeded' returns 'succeeded'.
     *
     * @return string The action
     */
    public function getAction(): string
    {
        $parts = explode('.', $this->type);

        return end($parts) ?: '';
    }

    /**
     * Get the resource type from the event.
     *
     * This extracts the first part of a dot-separated event type.
     * For example, 'payment_intent.succeeded' returns 'payment_intent'.
     *
     * @return string The resource type
     */
    public function getResourceType(): string
    {
        $parts = explode('.', $this->type);

        return $parts[0] ?? '';
    }

    /**
     * Get the category of this event.
     *
     * @return string The category constant
     */
    public function getCategory(): string
    {
        if ($this->isPaymentEvent()) {
            return self::CATEGORY_PAYMENT;
        }
        if ($this->isRefundEvent()) {
            return self::CATEGORY_REFUND;
        }
        if ($this->isDisputeEvent()) {
            return self::CATEGORY_DISPUTE;
        }
        if ($this->isSubscriptionEvent()) {
            return self::CATEGORY_SUBSCRIPTION;
        }
        if ($this->isInvoiceEvent()) {
            return self::CATEGORY_INVOICE;
        }
        if ($this->isSetupEvent()) {
            return self::CATEGORY_SETUP;
        }
        if ($this->isPaymentMethodEvent()) {
            return self::CATEGORY_PAYMENT_METHOD;
        }
        if ($this->isCustomerEvent()) {
            return self::CATEGORY_CUSTOMER;
        }
        if ($this->typeContains('payout')) {
            return self::CATEGORY_PAYOUT;
        }

        return $this->getResourceType();
    }

    /**
     * Convert the event to an array representation.
     *
     * @return array<string, mixed> The event data as an array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'data' => $this->data,
            'provider' => $this->provider,
            'apiVersion' => $this->apiVersion,
            'livemode' => $this->livemode,
            'createdAt' => $this->createdAt,
            'pendingWebhooks' => $this->pendingWebhooks,
            'requestId' => $this->requestId,
        ];
    }

    /**
     * Create a WebhookEvent instance from an array.
     *
     * @param  array<string, mixed>  $data  The event data array
     * @param  string|null  $provider  The payment provider name
     * @return self The created WebhookEvent instance
     */
    public static function fromArray(array $data, ?string $provider = null): self
    {
        return new self(
            id: $data['id'] ?? uniqid('evt_'),
            type: $data['type'] ?? '',
            data: $data['data'] ?? [],
            provider: $provider ?? $data['provider'] ?? null,
            apiVersion: $data['apiVersion'] ?? $data['api_version'] ?? null,
            livemode: $data['livemode'] ?? false,
            createdAt: $data['createdAt'] ?? $data['created'] ?? null,
            pendingWebhooks: $data['pendingWebhooks'] ?? $data['pending_webhooks'] ?? 0,
            requestId: $data['requestId'] ?? $data['request']['id'] ?? $data['request'] ?? null
        );
    }
}
