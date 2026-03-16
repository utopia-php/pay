<?php

namespace Utopia\Pay\Adapter\Stripe;

/**
 * Stripe-specific webhook event type constants.
 *
 * Contains all Stripe webhook event types for easy reference
 * and type-safe event handling.
 *
 * @see https://stripe.com/docs/api/events/types
 */
class StripeWebhookEvents
{
    // Payment Intent Events
    public const PAYMENT_INTENT_CREATED = 'payment_intent.created';

    public const PAYMENT_INTENT_SUCCEEDED = 'payment_intent.succeeded';

    public const PAYMENT_INTENT_FAILED = 'payment_intent.payment_failed';

    public const PAYMENT_INTENT_CANCELED = 'payment_intent.canceled';

    public const PAYMENT_INTENT_PROCESSING = 'payment_intent.processing';

    public const PAYMENT_INTENT_REQUIRES_ACTION = 'payment_intent.requires_action';

    public const PAYMENT_INTENT_AMOUNT_CAPTURABLE_UPDATED = 'payment_intent.amount_capturable_updated';

    public const PAYMENT_INTENT_PARTIALLY_FUNDED = 'payment_intent.partially_funded';

    // Charge Events
    public const CHARGE_SUCCEEDED = 'charge.succeeded';

    public const CHARGE_FAILED = 'charge.failed';

    public const CHARGE_PENDING = 'charge.pending';

    public const CHARGE_REFUNDED = 'charge.refunded';

    public const CHARGE_CAPTURED = 'charge.captured';

    public const CHARGE_UPDATED = 'charge.updated';

    public const CHARGE_EXPIRED = 'charge.expired';

    // Refund Events
    public const REFUND_CREATED = 'refund.created';

    public const REFUND_UPDATED = 'refund.updated';

    public const REFUND_FAILED = 'refund.failed';

    // Customer Events
    public const CUSTOMER_CREATED = 'customer.created';

    public const CUSTOMER_UPDATED = 'customer.updated';

    public const CUSTOMER_DELETED = 'customer.deleted';

    // Payment Method Events
    public const PAYMENT_METHOD_ATTACHED = 'payment_method.attached';

    public const PAYMENT_METHOD_DETACHED = 'payment_method.detached';

    public const PAYMENT_METHOD_UPDATED = 'payment_method.updated';

    public const PAYMENT_METHOD_AUTOMATICALLY_UPDATED = 'payment_method.automatically_updated';

    // Setup Intent Events
    public const SETUP_INTENT_CREATED = 'setup_intent.created';

    public const SETUP_INTENT_SUCCEEDED = 'setup_intent.succeeded';

    public const SETUP_INTENT_CANCELED = 'setup_intent.canceled';

    public const SETUP_INTENT_REQUIRES_ACTION = 'setup_intent.requires_action';

    public const SETUP_INTENT_SETUP_FAILED = 'setup_intent.setup_failed';

    // Dispute Events
    public const DISPUTE_CREATED = 'charge.dispute.created';

    public const DISPUTE_UPDATED = 'charge.dispute.updated';

    public const DISPUTE_CLOSED = 'charge.dispute.closed';

    public const DISPUTE_FUNDS_REINSTATED = 'charge.dispute.funds_reinstated';

    public const DISPUTE_FUNDS_WITHDRAWN = 'charge.dispute.funds_withdrawn';

    // Invoice Events
    public const INVOICE_CREATED = 'invoice.created';

    public const INVOICE_PAID = 'invoice.paid';

    public const INVOICE_PAYMENT_FAILED = 'invoice.payment_failed';

    public const INVOICE_PAYMENT_SUCCEEDED = 'invoice.payment_succeeded';

    public const INVOICE_UPCOMING = 'invoice.upcoming';

    public const INVOICE_FINALIZED = 'invoice.finalized';

    public const INVOICE_VOIDED = 'invoice.voided';

    public const INVOICE_MARKED_UNCOLLECTIBLE = 'invoice.marked_uncollectible';

    // Subscription Events
    public const SUBSCRIPTION_CREATED = 'customer.subscription.created';

    public const SUBSCRIPTION_UPDATED = 'customer.subscription.updated';

    public const SUBSCRIPTION_DELETED = 'customer.subscription.deleted';

    public const SUBSCRIPTION_PAUSED = 'customer.subscription.paused';

    public const SUBSCRIPTION_RESUMED = 'customer.subscription.resumed';

    public const SUBSCRIPTION_TRIAL_WILL_END = 'customer.subscription.trial_will_end';

    public const SUBSCRIPTION_PENDING_UPDATE_APPLIED = 'customer.subscription.pending_update_applied';

    public const SUBSCRIPTION_PENDING_UPDATE_EXPIRED = 'customer.subscription.pending_update_expired';

    // Payout Events
    public const PAYOUT_CREATED = 'payout.created';

    public const PAYOUT_PAID = 'payout.paid';

    public const PAYOUT_FAILED = 'payout.failed';

    public const PAYOUT_CANCELED = 'payout.canceled';

    public const PAYOUT_UPDATED = 'payout.updated';

    // Mandate Events
    public const MANDATE_UPDATED = 'mandate.updated';

    /**
     * Get all payment-related event types.
     *
     * @return array<string> List of payment event types
     */
    public static function getPaymentEvents(): array
    {
        return [
            self::PAYMENT_INTENT_CREATED,
            self::PAYMENT_INTENT_SUCCEEDED,
            self::PAYMENT_INTENT_FAILED,
            self::PAYMENT_INTENT_CANCELED,
            self::PAYMENT_INTENT_PROCESSING,
            self::PAYMENT_INTENT_REQUIRES_ACTION,
            self::CHARGE_SUCCEEDED,
            self::CHARGE_FAILED,
            self::CHARGE_PENDING,
            self::CHARGE_REFUNDED,
            self::CHARGE_CAPTURED,
        ];
    }

    /**
     * Get all dispute-related event types.
     *
     * @return array<string> List of dispute event types
     */
    public static function getDisputeEvents(): array
    {
        return [
            self::DISPUTE_CREATED,
            self::DISPUTE_UPDATED,
            self::DISPUTE_CLOSED,
            self::DISPUTE_FUNDS_REINSTATED,
            self::DISPUTE_FUNDS_WITHDRAWN,
        ];
    }

    /**
     * Get all subscription-related event types.
     *
     * @return array<string> List of subscription event types
     */
    public static function getSubscriptionEvents(): array
    {
        return [
            self::SUBSCRIPTION_CREATED,
            self::SUBSCRIPTION_UPDATED,
            self::SUBSCRIPTION_DELETED,
            self::SUBSCRIPTION_PAUSED,
            self::SUBSCRIPTION_RESUMED,
            self::SUBSCRIPTION_TRIAL_WILL_END,
        ];
    }

    /**
     * Get recommended events for basic payment integration.
     *
     * @return array<string> List of essential event types
     */
    public static function getEssentialEvents(): array
    {
        return [
            self::PAYMENT_INTENT_SUCCEEDED,
            self::PAYMENT_INTENT_FAILED,
            self::CHARGE_REFUNDED,
            self::DISPUTE_CREATED,
            self::CUSTOMER_DELETED,
        ];
    }

    /**
     * Get all success event types.
     *
     * @return array<string> List of success event types
     */
    public static function getSuccessEvents(): array
    {
        return [
            self::PAYMENT_INTENT_SUCCEEDED,
            self::CHARGE_SUCCEEDED,
            self::CHARGE_CAPTURED,
            self::SETUP_INTENT_SUCCEEDED,
            self::INVOICE_PAID,
            self::INVOICE_PAYMENT_SUCCEEDED,
            self::PAYOUT_PAID,
        ];
    }

    /**
     * Get all failure event types.
     *
     * @return array<string> List of failure event types
     */
    public static function getFailureEvents(): array
    {
        return [
            self::PAYMENT_INTENT_FAILED,
            self::CHARGE_FAILED,
            self::REFUND_FAILED,
            self::SETUP_INTENT_SETUP_FAILED,
            self::INVOICE_PAYMENT_FAILED,
            self::PAYOUT_FAILED,
        ];
    }

    /**
     * Get events that require immediate action.
     *
     * @return array<string> List of action-required event types
     */
    public static function getActionRequiredEvents(): array
    {
        return [
            self::PAYMENT_INTENT_REQUIRES_ACTION,
            self::SETUP_INTENT_REQUIRES_ACTION,
            self::DISPUTE_CREATED,
            self::SUBSCRIPTION_TRIAL_WILL_END,
        ];
    }
}
