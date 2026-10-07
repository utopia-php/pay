<?php

namespace Utopia\Pay;

class Exception extends \Exception
{
    public const GENERAL_UNKNOWN = 'general_unknown';

    public const AUTHENTICATION_REQUIRED = 'authentication_required';

    public const INSUFFICIENT_FUNDS = 'insufficient_funds';

    public const INCORRECT_NUMBER = 'incorrect_number';

    public const GENERIC_DECLINE = 'generic_decline';

    /** @param array<mixed> $metadata */
    public function __construct(
        public readonly string $type = self::GENERAL_UNKNOWN,
        ?string $message = null,
        ?int $code = null,
        public readonly array $metadata = [],
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message ?? 'Unknown error', $code ?? 500, $previous);
    }
}
