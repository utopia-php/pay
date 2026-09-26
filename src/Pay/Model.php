<?php

namespace Utopia\Pay;

/**
 * Read-only view over a gateway payload. Getters return null when a field is absent or has
 * an unexpected type, so callers keep their own fallbacks instead of inheriting invented ones.
 */
abstract class Model
{
    /**
     * @param  array<string, mixed>  $data
     */
    final public function __construct(private readonly array $data)
    {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): static
    {
        return new static($data);
    }

    /**
     * The payload as the gateway returned it, for fields that have no getter
     *
     * @return array<string, mixed>
     */
    public function getRaw(): array
    {
        return $this->data;
    }

    protected function value(string ...$path): mixed
    {
        $value = $this->data;
        foreach ($path as $key) {
            if (! is_array($value) || ! array_key_exists($key, $value)) {
                return null;
            }
            $value = $value[$key];
        }

        return $value;
    }

    protected function string(string ...$path): ?string
    {
        $value = $this->value(...$path);

        return is_string($value) ? $value : null;
    }

    protected function int(string ...$path): ?int
    {
        $value = $this->value(...$path);

        return is_int($value) ? $value : null;
    }

    protected function bool(string ...$path): ?bool
    {
        $value = $this->value(...$path);

        return is_bool($value) ? $value : null;
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function array(string ...$path): ?array
    {
        $value = $this->value(...$path);

        return is_array($value) ? $value : null;
    }

    /**
     * Related objects come back as an ID, or as the full object when expanded
     */
    protected function expandableId(string ...$path): ?string
    {
        $value = $this->value(...$path);
        if (is_array($value)) {
            $value = $value['id'] ?? null;
        }

        return is_string($value) ? $value : null;
    }

    /**
     * Stripe list objects keep their items under `data`
     *
     * @template T of Model
     *
     * @param  class-string<T>  $class
     * @return array<T>
     */
    protected function list(string $class, string ...$path): array
    {
        $items = $this->array(...[...$path, 'data']) ?? [];

        return array_values(array_map(fn ($item) => $class::fromArray(is_array($item) ? $item : []), $items));
    }
}
