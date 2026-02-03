<?php

namespace Utopia\Pay;

/**
 * Exception class for payment-related errors.
 *
 * Extends PHP's Exception with payment-specific error types and metadata.
 */
class Exception extends \Exception
{
    // General errors
    public const GENERAL_UNKNOWN = 'general_unknown';

    public const GENERAL_RATE_LIMIT = 'rate_limit';

    public const GENERAL_API_ERROR = 'api_error';

    public const GENERAL_INVALID_REQUEST = 'invalid_request_error';

    public const GENERAL_CONNECTION_ERROR = 'connection_error';

    // Authentication errors
    public const AUTHENTICATION_REQUIRED = 'authentication_required';

    public const AUTHENTICATION_FAILED = 'authentication_failed';

    public const INVALID_API_KEY = 'invalid_api_key';

    // Card errors
    public const INSUFFICIENT_FUNDS = 'insufficient_funds';

    public const INCORRECT_NUMBER = 'incorrect_number';

    public const GENERIC_DECLINE = 'generic_decline';

    public const CARD_DECLINED = 'card_declined';

    public const EXPIRED_CARD = 'expired_card';

    public const INCORRECT_CVC = 'incorrect_cvc';

    public const INCORRECT_ZIP = 'incorrect_zip';

    public const INVALID_EXPIRY_MONTH = 'invalid_expiry_month';

    public const INVALID_EXPIRY_YEAR = 'invalid_expiry_year';

    public const PROCESSING_ERROR = 'processing_error';

    public const CARD_NOT_SUPPORTED = 'card_not_supported';

    public const CURRENCY_NOT_SUPPORTED = 'currency_not_supported';

    public const DUPLICATE_TRANSACTION = 'duplicate_transaction';

    public const FRAUDULENT = 'fraudulent';

    public const LOST_CARD = 'lost_card';

    public const STOLEN_CARD = 'stolen_card';

    public const DO_NOT_HONOR = 'do_not_honor';

    // Customer errors
    public const CUSTOMER_NOT_FOUND = 'customer_not_found';

    public const CUSTOMER_TAX_LOCATION_INVALID = 'customer_tax_location_invalid';

    // Payment method errors
    public const PAYMENT_METHOD_NOT_FOUND = 'payment_method_not_found';

    public const PAYMENT_METHOD_INVALID = 'payment_method_invalid';

    public const PAYMENT_METHOD_UNAVAILABLE = 'payment_method_unavailable';

    // Payment intent errors
    public const PAYMENT_INTENT_NOT_FOUND = 'payment_intent_not_found';

    public const PAYMENT_INTENT_INVALID_STATE = 'payment_intent_invalid_state';

    public const PAYMENT_INTENT_UNEXPECTED_STATE = 'payment_intent_unexpected_state';

    public const AMOUNT_TOO_SMALL = 'amount_too_small';

    public const AMOUNT_TOO_LARGE = 'amount_too_large';

    // Refund errors
    public const REFUND_NOT_FOUND = 'refund_not_found';

    public const REFUND_FAILED = 'refund_failed';

    public const CHARGE_ALREADY_REFUNDED = 'charge_already_refunded';

    public const CHARGE_DISPUTE_EXISTS = 'charge_dispute_exists';

    protected string $type = '';

    /**
     * Metadata object with additional error data
     *
     * @var array<string, mixed>
     */
    protected array $metadata = [];

    /**
     * Create a new Exception instance.
     *
     * @param  string  $type  The error type (use class constants)
     * @param  string|null  $message  Human-readable error message
     * @param  int|null  $code  HTTP status code
     * @param  array<string, mixed>  $metadata  Additional error metadata
     * @param  \Throwable|null  $previous  Previous exception for chaining
     */
    public function __construct(string $type = Exception::GENERAL_UNKNOWN, string $message = null, int $code = null, array $metadata = [], \Throwable $previous = null)
    {
        $this->type = $type;
        $this->code = $code ?? 500;
        $this->metadata = $metadata;

        $this->message = $message ?? 'Unknown error';

        parent::__construct($this->message, $this->code, $previous);
    }

    /**
     * Get the type of the exception.
     *
     * @return string The error type
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Set the type of the exception.
     *
     * @param  string  $type  The error type
     * @return void
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * Get metadata object.
     *
     * @return array<string, mixed> The metadata array
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    /**
     * Set metadata object.
     *
     * @param  array<string, mixed>  $metadata  The metadata array
     * @return void
     */
    public function setMetadata(array $metadata): void
    {
        $this->metadata = $metadata;
    }

    /**
     * Check if this is a card error.
     *
     * @return bool True if this is a card-related error
     */
    public function isCardError(): bool
    {
        return in_array($this->type, [
            self::INSUFFICIENT_FUNDS,
            self::INCORRECT_NUMBER,
            self::GENERIC_DECLINE,
            self::CARD_DECLINED,
            self::EXPIRED_CARD,
            self::INCORRECT_CVC,
            self::INCORRECT_ZIP,
            self::INVALID_EXPIRY_MONTH,
            self::INVALID_EXPIRY_YEAR,
            self::CARD_NOT_SUPPORTED,
            self::LOST_CARD,
            self::STOLEN_CARD,
            self::DO_NOT_HONOR,
            self::FRAUDULENT,
        ]);
    }

    /**
     * Check if this is an authentication error.
     *
     * @return bool True if this is an authentication-related error
     */
    public function isAuthenticationError(): bool
    {
        return in_array($this->type, [
            self::AUTHENTICATION_REQUIRED,
            self::AUTHENTICATION_FAILED,
            self::INVALID_API_KEY,
        ]);
    }

    /**
     * Check if this error is retryable.
     *
     * @return bool True if the operation can be retried
     */
    public function isRetryable(): bool
    {
        return in_array($this->type, [
            self::GENERAL_RATE_LIMIT,
            self::GENERAL_CONNECTION_ERROR,
            self::PROCESSING_ERROR,
        ]);
    }

    /**
     * Check if this error requires user action.
     *
     * @return bool True if user needs to take action
     */
    public function requiresUserAction(): bool
    {
        return in_array($this->type, [
            self::AUTHENTICATION_REQUIRED,
            self::INSUFFICIENT_FUNDS,
            self::INCORRECT_NUMBER,
            self::EXPIRED_CARD,
            self::INCORRECT_CVC,
            self::INCORRECT_ZIP,
            self::INVALID_EXPIRY_MONTH,
            self::INVALID_EXPIRY_YEAR,
        ]);
    }

    /**
     * Get a user-friendly error message based on the error type.
     *
     * @return string A user-friendly error message
     */
    public function getUserMessage(): string
    {
        return match ($this->type) {
            self::INSUFFICIENT_FUNDS => 'Your card has insufficient funds. Please try a different payment method.',
            self::INCORRECT_NUMBER => 'The card number is incorrect. Please check and try again.',
            self::EXPIRED_CARD => 'Your card has expired. Please use a different card.',
            self::INCORRECT_CVC => 'The security code (CVC) is incorrect. Please check and try again.',
            self::INCORRECT_ZIP => 'The postal code is incorrect. Please check and try again.',
            self::INVALID_EXPIRY_MONTH => 'The expiration month is invalid. Please check and try again.',
            self::INVALID_EXPIRY_YEAR => 'The expiration year is invalid. Please check and try again.',
            self::CARD_DECLINED, self::GENERIC_DECLINE => 'Your card was declined. Please try a different payment method.',
            self::CARD_NOT_SUPPORTED => 'This card type is not supported. Please try a different card.',
            self::CURRENCY_NOT_SUPPORTED => 'This currency is not supported.',
            self::AUTHENTICATION_REQUIRED => 'Additional authentication is required to complete this payment.',
            self::LOST_CARD, self::STOLEN_CARD => 'Your card was declined. Please contact your card issuer.',
            self::FRAUDULENT => 'This payment was flagged as potentially fraudulent.',
            self::DUPLICATE_TRANSACTION => 'This appears to be a duplicate transaction.',
            self::GENERAL_RATE_LIMIT => 'Too many requests. Please try again in a moment.',
            self::AMOUNT_TOO_SMALL => 'The payment amount is too small.',
            self::AMOUNT_TOO_LARGE => 'The payment amount is too large.',
            default => 'An error occurred while processing your payment. Please try again.',
        };
    }

    /**
     * Convert the exception to an array representation.
     *
     * @return array<string, mixed> The exception data as an array
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'message' => $this->message,
            'code' => $this->code,
            'metadata' => $this->metadata,
            'userMessage' => $this->getUserMessage(),
            'isRetryable' => $this->isRetryable(),
            'requiresUserAction' => $this->requiresUserAction(),
        ];
    }
}
