<?php

namespace Utopia\Pay\Pagination;

/**
 * Cursor class for pagination parameters.
 *
 * A provider-agnostic class for specifying pagination options
 * when making list requests.
 */
class Cursor
{
    /**
     * Default limit for list operations.
     */
    public const DEFAULT_LIMIT = 10;

    /**
     * Maximum limit for list operations.
     */
    public const MAX_LIMIT = 100;

    /**
     * Create a new Cursor instance.
     *
     * @param  int  $limit  Maximum number of results to return
     * @param  string|null  $startingAfter  Return results after this ID (cursor)
     * @param  string|null  $endingBefore  Return results before this ID (cursor)
     */
    public function __construct(
        private int $limit = self::DEFAULT_LIMIT,
        private ?string $startingAfter = null,
        private ?string $endingBefore = null
    ) {
        $this->limit = min(max(1, $limit), self::MAX_LIMIT);
    }

    /**
     * Get the limit.
     *
     * @return int The limit
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * Set the limit.
     *
     * @param  int  $limit  The limit (1-100)
     * @return static
     */
    public function setLimit(int $limit): static
    {
        $this->limit = min(max(1, $limit), self::MAX_LIMIT);

        return $this;
    }

    /**
     * Get the starting after cursor.
     *
     * @return string|null The cursor
     */
    public function getStartingAfter(): ?string
    {
        return $this->startingAfter;
    }

    /**
     * Set the starting after cursor.
     *
     * @param  string|null  $startingAfter  The cursor
     * @return static
     */
    public function setStartingAfter(?string $startingAfter): static
    {
        $this->startingAfter = $startingAfter;

        return $this;
    }

    /**
     * Get the ending before cursor.
     *
     * @return string|null The cursor
     */
    public function getEndingBefore(): ?string
    {
        return $this->endingBefore;
    }

    /**
     * Set the ending before cursor.
     *
     * @param  string|null  $endingBefore  The cursor
     * @return static
     */
    public function setEndingBefore(?string $endingBefore): static
    {
        $this->endingBefore = $endingBefore;

        return $this;
    }

    /**
     * Check if this cursor has a starting after value.
     *
     * @return bool True if has starting after
     */
    public function hasStartingAfter(): bool
    {
        return $this->startingAfter !== null;
    }

    /**
     * Check if this cursor has an ending before value.
     *
     * @return bool True if has ending before
     */
    public function hasEndingBefore(): bool
    {
        return $this->endingBefore !== null;
    }

    /**
     * Convert to array for API requests.
     *
     * @return array<string, mixed> The cursor parameters
     */
    public function toArray(): array
    {
        $params = ['limit' => $this->limit];

        if ($this->startingAfter !== null) {
            $params['starting_after'] = $this->startingAfter;
        }

        if ($this->endingBefore !== null) {
            $params['ending_before'] = $this->endingBefore;
        }

        return $params;
    }

    /**
     * Create cursor for the next page based on a result.
     *
     * @param  PaginatedResult<mixed>  $result  The current result
     * @return static|null New cursor for next page or null
     */
    public static function forNextPage(PaginatedResult $result): ?static
    {
        $nextCursor = $result->getNextCursor();

        if ($nextCursor === null) {
            return null;
        }

        return new static(
            limit: $result->getLimit() ?? self::DEFAULT_LIMIT,
            startingAfter: $nextCursor
        );
    }

    /**
     * Create cursor for the previous page based on a result.
     *
     * @param  PaginatedResult<mixed>  $result  The current result
     * @return static|null New cursor for previous page or null
     */
    public static function forPreviousPage(PaginatedResult $result): ?static
    {
        $previousCursor = $result->getPreviousCursor();

        if ($previousCursor === null) {
            return null;
        }

        return new static(
            limit: $result->getLimit() ?? self::DEFAULT_LIMIT,
            endingBefore: $previousCursor
        );
    }

    /**
     * Create a new cursor with default settings.
     *
     * @param  int  $limit  The limit
     * @return static
     */
    public static function create(int $limit = self::DEFAULT_LIMIT): static
    {
        return new static($limit);
    }

    /**
     * Create a cursor starting after a specific ID.
     *
     * @param  string  $id  The ID to start after
     * @param  int  $limit  The limit
     * @return static
     */
    public static function after(string $id, int $limit = self::DEFAULT_LIMIT): static
    {
        return new static($limit, startingAfter: $id);
    }

    /**
     * Create a cursor ending before a specific ID.
     *
     * @param  string  $id  The ID to end before
     * @param  int  $limit  The limit
     * @return static
     */
    public static function before(string $id, int $limit = self::DEFAULT_LIMIT): static
    {
        return new static($limit, endingBefore: $id);
    }
}
