<?php

namespace Utopia\Pay;

class Exception extends \Exception
{
    public const GENERAL_UNKNOWN = 'general_unknown';

    public const AUTHENTICATION_REQUIRED = 'authentication_required';

    public const INSUFFICIENT_FUNDS = 'insufficient_funds';

    public const INCORRECT_NUMBER = 'incorrect_number';

    public const GENERIC_DECLINE = 'generic_decline';

    public const CARD_DECLINED = 'card_declined';

    public const EXPIRED_CARD = 'expired_card';

    public const INCORRECT_CVC = 'incorrect_cvc';

    public const LOST_CARD = 'lost_card';

    public const STOLEN_CARD = 'stolen_card';

    public const FRAUDULENT = 'fraudulent';

    public const DO_NOT_HONOR = 'do_not_honor';

    public const PROCESSING_ERROR = 'processing_error';

    public const AMOUNT_TOO_SMALL = 'amount_too_small';

    public const PAYMENT_INTENT_UNEXPECTED_STATE = 'payment_intent_unexpected_state';

    public const RESOURCE_MISSING = 'resource_missing';

    public const RATE_LIMIT = 'rate_limit';

    protected string $type = '';

    /**
     * Metadata object with additional error data
     *
     * @var array
     */
    protected array $metadata = [];

    public function __construct(string $type = Exception::GENERAL_UNKNOWN, ?string $message = null, ?int $code = null, array $metadata = [], ?\Throwable $previous = null)
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
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Set the type of the exception.
     *
     * @param  string  $type
     * @return void
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * Get metadata object.
     *
     * @return string
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    /**
     * Set metadata object.
     *
     * @param  array  $metadata
     * @return void
     */
    public function setMetadata(array $metadata): void
    {
        $this->metadata = $metadata;
    }
}
