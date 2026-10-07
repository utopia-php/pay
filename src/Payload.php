<?php

declare(strict_types=1);

namespace Utopia\Pay;

/** Validates untrusted processor JSON before it enters the typed API. @internal */
final readonly class Payload
{
    public function __construct(private \stdClass $data)
    {
    }

    public function string(string $key): ?string
    {
        $value = $this->data->$key ?? null;
        if ($value !== null && !is_string($value)) {
            throw new Exception(code: 502, message: "Expected string: $key");
        }
        return $value;
    }

    /**
     * @template T of \BackedEnum
     * @param class-string<T> $enum
     * @return T|null
     */
    public function enumValue(string $key, string $enum): ?\BackedEnum
    {
        $value = $this->string($key);
        return $value === null ? null : ($enum::tryFrom($value) ?? throw new Exception(message: "Unknown processor value for $key: $value", code: 502));
    }

    /** Expandable references may be an ID or an object. */
    public function expandedObject(string $key): ?self
    {
        $value = $this->data->$key ?? null;
        return is_string($value) ? null : $this->object($key);
    }

    public function integer(string $key): ?int
    {
        $value = $this->data->$key ?? null;
        if ($value !== null && !is_int($value)) {
            throw new Exception(code: 502, message: "Expected integer: $key");
        }
        return $value;
    }

    public function boolean(string $key): ?bool
    {
        $value = $this->data->$key ?? null;
        if ($value !== null && !is_bool($value)) {
            throw new Exception(code: 502, message: "Expected boolean: $key");
        }
        return $value;
    }

    public function object(string $key): ?self
    {
        $value = $this->data->$key ?? null;
        if ($value === null) {
            return null;
        }
        if (!$value instanceof \stdClass) {
            throw new Exception(code: 502, message: "Expected object: $key");
        }
        return new self($value);
    }

    public function reference(string $key): ?string
    {
        $value = $this->data->$key ?? null;
        return $value instanceof \stdClass ? new self($value)->string('id') : $this->string($key);
    }

    /** @return list<self> */
    public function objects(string $key): array
    {
        $value = $this->data->$key ?? [];
        if (!is_array($value) || !array_is_list($value)) {
            throw new Exception(code: 502, message: "Expected list: $key");
        }
        return array_map(static function (mixed $item): self {
            if (!$item instanceof \stdClass) {
                throw new Exception(code: 502, message: 'Expected list of objects');
            }
            return new self($item);
        }, $value);
    }

    /** @return array<array-key, string> */
    public function metadata(): array
    {
        $value = $this->data->metadata ?? new \stdClass();
        if (!$value instanceof \stdClass) {
            throw new Exception(code: 502, message: 'Expected metadata object');
        }
        $metadata = [];
        foreach (get_object_vars($value) as $key => $item) {
            if (!is_string($item)) {
                throw new Exception(code: 502, message: 'Expected string metadata values');
            }
            $metadata[$key] = $item;
        }
        return $metadata;
    }
}
