<?php

namespace Utopia\Pay;

trait Expandable
{
    /**
     * Related objects come back as an ID, or as the full object when expanded.
     */
    protected static function expandableId(mixed $value): ?string
    {
        if (is_array($value)) {
            $value = $value['id'] ?? null;
        }

        return is_string($value) ? $value : null;
    }
}
