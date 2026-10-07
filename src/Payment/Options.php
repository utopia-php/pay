<?php

declare(strict_types=1);

namespace Utopia\Pay\Payment;

final readonly class Options
{
    /** @param array<array-key, string> $metadata */
    public function __construct(
        private ?bool $offSession = null,
        private ?bool $confirm = null,
        public ?string $mandate = null,
        private ?string $statementDescriptor = null,
        private ?string $description = null,
        private array $metadata = [],
    ) {
    }

    /** @return array{off_session?: string, confirm?: string, mandate?: string, statement_descriptor?: string, description?: string, metadata?: array<array-key, string>} */
    public function toArray(): array
    {
        $params = [];
        if ($this->offSession !== null) {
            $params['off_session'] = $this->offSession ? 'true' : 'false';
        }
        if ($this->confirm !== null) {
            $params['confirm'] = $this->confirm ? 'true' : 'false';
        }
        if ($this->mandate !== null) {
            $params['mandate'] = $this->mandate;
        }
        if ($this->statementDescriptor !== null) {
            $params['statement_descriptor'] = $this->statementDescriptor;
        }
        if ($this->description !== null) {
            $params['description'] = $this->description;
        }
        if ($this->metadata !== []) {
            $params['metadata'] = $this->metadata;
        }
        return $params;
    }
}
