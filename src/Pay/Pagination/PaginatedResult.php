<?php

namespace Utopia\Pay\Pagination;

/**
 * PaginatedResult class for handling paginated API responses.
 *
 * A provider-agnostic class that represents a page of results
 * from list operations, with support for cursor-based and
 * offset-based pagination.
 *
 * @template T
 */
class PaginatedResult
{
    /**
     * Create a new PaginatedResult instance.
     *
     * @param  array<T>  $data  The items in this page
     * @param  bool  $hasMore  Whether there are more results
     * @param  string|null  $startingAfter  Cursor for the first item
     * @param  string|null  $endingBefore  Cursor for the last item
     * @param  int|null  $totalCount  Total count if available
     * @param  int|null  $limit  The limit used for this request
     */
    public function __construct(
        private array $data,
        private bool $hasMore = false,
        private ?string $startingAfter = null,
        private ?string $endingBefore = null,
        private ?int $totalCount = null,
        private ?int $limit = null
    ) {
    }

    /**
     * Get the items in this page.
     *
     * @return array<T> The items
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Check if there are more results.
     *
     * @return bool True if more results exist
     */
    public function hasMore(): bool
    {
        return $this->hasMore;
    }

    /**
     * Get the cursor for fetching the next page.
     *
     * Use this value as the 'starting_after' parameter
     * to fetch the next page of results.
     *
     * @return string|null The cursor or null if no more pages
     */
    public function getNextCursor(): ?string
    {
        if (! $this->hasMore || empty($this->data)) {
            return null;
        }

        $lastItem = end($this->data);
        if (is_object($lastItem) && method_exists($lastItem, 'getId')) {
            return $lastItem->getId();
        }
        if (is_array($lastItem) && isset($lastItem['id'])) {
            return $lastItem['id'];
        }

        return $this->startingAfter;
    }

    /**
     * Get the cursor for fetching the previous page.
     *
     * Use this value as the 'ending_before' parameter
     * to fetch the previous page of results.
     *
     * @return string|null The cursor or null
     */
    public function getPreviousCursor(): ?string
    {
        if (empty($this->data)) {
            return null;
        }

        $firstItem = reset($this->data);
        if (is_object($firstItem) && method_exists($firstItem, 'getId')) {
            return $firstItem->getId();
        }
        if (is_array($firstItem) && isset($firstItem['id'])) {
            return $firstItem['id'];
        }

        return $this->endingBefore;
    }

    /**
     * Get the starting after cursor that was used.
     *
     * @return string|null The cursor
     */
    public function getStartingAfter(): ?string
    {
        return $this->startingAfter;
    }

    /**
     * Get the ending before cursor that was used.
     *
     * @return string|null The cursor
     */
    public function getEndingBefore(): ?string
    {
        return $this->endingBefore;
    }

    /**
     * Get the total count of all results (if available).
     *
     * Note: Not all providers support total counts.
     *
     * @return int|null The total count or null
     */
    public function getTotalCount(): ?int
    {
        return $this->totalCount;
    }

    /**
     * Get the limit used for this request.
     *
     * @return int|null The limit
     */
    public function getLimit(): ?int
    {
        return $this->limit;
    }

    /**
     * Get the number of items in this page.
     *
     * @return int The count
     */
    public function count(): int
    {
        return count($this->data);
    }

    /**
     * Check if this page is empty.
     *
     * @return bool True if no items
     */
    public function isEmpty(): bool
    {
        return empty($this->data);
    }

    /**
     * Get the first item in this page.
     *
     * @return T|null The first item or null
     */
    public function first(): mixed
    {
        return $this->data[0] ?? null;
    }

    /**
     * Get the last item in this page.
     *
     * @return T|null The last item or null
     */
    public function last(): mixed
    {
        if (empty($this->data)) {
            return null;
        }

        return end($this->data);
    }

    /**
     * Convert to array representation.
     *
     * @return array<string, mixed> The paginated result as array
     */
    public function toArray(): array
    {
        return [
            'data' => array_map(function ($item) {
                if (is_object($item) && method_exists($item, 'toArray')) {
                    return $item->toArray();
                }

                return $item;
            }, $this->data),
            'hasMore' => $this->hasMore,
            'totalCount' => $this->totalCount,
            'limit' => $this->limit,
        ];
    }

    /**
     * Create a PaginatedResult from a provider response.
     *
     * @param  array<string, mixed>  $response  The provider response
     * @param  callable|null  $itemMapper  Optional function to map items
     * @param  int|null  $limit  The limit that was used
     * @return self<mixed> The paginated result
     */
    public static function fromResponse(array $response, ?callable $itemMapper = null, ?int $limit = null): self
    {
        $data = $response['data'] ?? [];

        if ($itemMapper !== null) {
            $data = array_map($itemMapper, $data);
        }

        return new self(
            data: $data,
            hasMore: $response['has_more'] ?? $response['hasMore'] ?? false,
            totalCount: $response['total_count'] ?? $response['totalCount'] ?? null,
            limit: $limit
        );
    }
}
