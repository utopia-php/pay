<?php

namespace Utopia\Pay;

class Exception extends \Exception
{
    public const GENERAL_UNKNOWN = 'general_unknown';

    public const AUTHENTICATION_REQUIRED = 'authentication_required';

    public const INSUFFICIENT_FUNDS = 'insufficient_funds';

    public const INCORRECT_NUMBER = 'incorrect_number';

    public const GENERIC_DECLINE = 'generic_decline';

    protected string $type = '';

    public function __construct(string $type = Exception::GENERAL_UNKNOWN, string $message = null, int $code = null, \Throwable $previous = null)
    {
        $this->type = $type;
        $this->code = $code ?? 500;

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
}
