<?php

namespace Utopia\Tests;

use PHPUnit\Framework\TestCase;
use Utopia\Pay\Pagination\Cursor;
use Utopia\Pay\Pagination\PaginatedResult;

class PaginationTest extends TestCase
{
    // ---- Cursor Tests ----

    public function testCursorConstructor(): void
    {
        $cursor = new Cursor(25, 'item_after', 'item_before');

        $this->assertEquals(25, $cursor->getLimit());
        $this->assertEquals('item_after', $cursor->getStartingAfter());
        $this->assertEquals('item_before', $cursor->getEndingBefore());
    }

    public function testCursorDefaults(): void
    {
        $cursor = new Cursor();

        $this->assertEquals(Cursor::DEFAULT_LIMIT, $cursor->getLimit());
        $this->assertNull($cursor->getStartingAfter());
        $this->assertNull($cursor->getEndingBefore());
    }

    public function testCursorLimitClamping(): void
    {
        // Below minimum
        $cursor = new Cursor(0);
        $this->assertEquals(1, $cursor->getLimit());

        $cursor = new Cursor(-5);
        $this->assertEquals(1, $cursor->getLimit());

        // Above maximum
        $cursor = new Cursor(200);
        $this->assertEquals(Cursor::MAX_LIMIT, $cursor->getLimit());
    }

    public function testCursorSetLimit(): void
    {
        $cursor = new Cursor();
        $result = $cursor->setLimit(50);

        $this->assertEquals(50, $cursor->getLimit());
        $this->assertSame($cursor, $result); // fluent

        // Clamping on setter too
        $cursor->setLimit(0);
        $this->assertEquals(1, $cursor->getLimit());

        $cursor->setLimit(999);
        $this->assertEquals(Cursor::MAX_LIMIT, $cursor->getLimit());
    }

    public function testCursorSetStartingAfter(): void
    {
        $cursor = new Cursor();
        $result = $cursor->setStartingAfter('item_123');

        $this->assertEquals('item_123', $cursor->getStartingAfter());
        $this->assertSame($cursor, $result);
    }

    public function testCursorSetEndingBefore(): void
    {
        $cursor = new Cursor();
        $result = $cursor->setEndingBefore('item_456');

        $this->assertEquals('item_456', $cursor->getEndingBefore());
        $this->assertSame($cursor, $result);
    }

    public function testCursorHasStartingAfter(): void
    {
        $cursor = new Cursor();
        $this->assertFalse($cursor->hasStartingAfter());

        $cursor->setStartingAfter('item_123');
        $this->assertTrue($cursor->hasStartingAfter());
    }

    public function testCursorHasEndingBefore(): void
    {
        $cursor = new Cursor();
        $this->assertFalse($cursor->hasEndingBefore());

        $cursor->setEndingBefore('item_456');
        $this->assertTrue($cursor->hasEndingBefore());
    }

    public function testCursorToArray(): void
    {
        $cursor = new Cursor(25, 'item_after', 'item_before');
        $array = $cursor->toArray();

        $this->assertEquals(25, $array['limit']);
        $this->assertEquals('item_after', $array['starting_after']);
        $this->assertEquals('item_before', $array['ending_before']);
    }

    public function testCursorToArrayMinimal(): void
    {
        $cursor = new Cursor(10);
        $array = $cursor->toArray();

        $this->assertEquals(10, $array['limit']);
        $this->assertArrayNotHasKey('starting_after', $array);
        $this->assertArrayNotHasKey('ending_before', $array);
    }

    public function testCursorCreate(): void
    {
        $cursor = Cursor::create(50);
        $this->assertEquals(50, $cursor->getLimit());
        $this->assertNull($cursor->getStartingAfter());
        $this->assertNull($cursor->getEndingBefore());
    }

    public function testCursorAfter(): void
    {
        $cursor = Cursor::after('item_123', 25);
        $this->assertEquals(25, $cursor->getLimit());
        $this->assertEquals('item_123', $cursor->getStartingAfter());
        $this->assertNull($cursor->getEndingBefore());
    }

    public function testCursorBefore(): void
    {
        $cursor = Cursor::before('item_456', 25);
        $this->assertEquals(25, $cursor->getLimit());
        $this->assertNull($cursor->getStartingAfter());
        $this->assertEquals('item_456', $cursor->getEndingBefore());
    }

    public function testCursorConstants(): void
    {
        $this->assertEquals(10, Cursor::DEFAULT_LIMIT);
        $this->assertEquals(100, Cursor::MAX_LIMIT);
    }

    // ---- PaginatedResult Tests ----

    public function testPaginatedResultConstructor(): void
    {
        $items = [['id' => '1'], ['id' => '2'], ['id' => '3']];
        $result = new PaginatedResult($items, true, 'start', 'end', 100, 10);

        $this->assertEquals($items, $result->getData());
        $this->assertTrue($result->hasMore());
        $this->assertEquals('start', $result->getStartingAfter());
        $this->assertEquals('end', $result->getEndingBefore());
        $this->assertEquals(100, $result->getTotalCount());
        $this->assertEquals(10, $result->getLimit());
    }

    public function testPaginatedResultDefaults(): void
    {
        $result = new PaginatedResult([]);

        $this->assertEquals([], $result->getData());
        $this->assertFalse($result->hasMore());
        $this->assertNull($result->getStartingAfter());
        $this->assertNull($result->getEndingBefore());
        $this->assertNull($result->getTotalCount());
        $this->assertNull($result->getLimit());
    }

    public function testPaginatedResultCount(): void
    {
        $result = new PaginatedResult([1, 2, 3]);
        $this->assertEquals(3, $result->count());

        $empty = new PaginatedResult([]);
        $this->assertEquals(0, $empty->count());
    }

    public function testPaginatedResultIsEmpty(): void
    {
        $result = new PaginatedResult([1, 2]);
        $this->assertFalse($result->isEmpty());

        $empty = new PaginatedResult([]);
        $this->assertTrue($empty->isEmpty());
    }

    public function testPaginatedResultFirst(): void
    {
        $result = new PaginatedResult(['a', 'b', 'c']);
        $this->assertEquals('a', $result->first());

        $empty = new PaginatedResult([]);
        $this->assertNull($empty->first());
    }

    public function testPaginatedResultLast(): void
    {
        $result = new PaginatedResult(['a', 'b', 'c']);
        $this->assertEquals('c', $result->last());

        $empty = new PaginatedResult([]);
        $this->assertNull($empty->last());
    }

    public function testGetNextCursorWithArrayItems(): void
    {
        $items = [['id' => '1'], ['id' => '2'], ['id' => '3']];
        $result = new PaginatedResult($items, true);

        $this->assertEquals('3', $result->getNextCursor());
    }

    public function testGetNextCursorNoMore(): void
    {
        $items = [['id' => '1'], ['id' => '2']];
        $result = new PaginatedResult($items, false);

        $this->assertNull($result->getNextCursor());
    }

    public function testGetNextCursorEmpty(): void
    {
        $result = new PaginatedResult([], true);
        $this->assertNull($result->getNextCursor());
    }

    public function testGetPreviousCursorWithArrayItems(): void
    {
        $items = [['id' => '4'], ['id' => '5'], ['id' => '6']];
        $result = new PaginatedResult($items, true);

        $this->assertEquals('4', $result->getPreviousCursor());
    }

    public function testGetPreviousCursorEmpty(): void
    {
        $result = new PaginatedResult([]);
        $this->assertNull($result->getPreviousCursor());
    }

    public function testToArray(): void
    {
        $items = [['id' => '1'], ['id' => '2']];
        $result = new PaginatedResult($items, true, null, null, 50, 10);

        $array = $result->toArray();

        $this->assertIsArray($array);
        $this->assertEquals($items, $array['data']);
        $this->assertTrue($array['hasMore']);
        $this->assertEquals(50, $array['totalCount']);
        $this->assertEquals(10, $array['limit']);
    }

    public function testFromResponse(): void
    {
        $response = [
            'data' => [['id' => '1'], ['id' => '2']],
            'has_more' => true,
            'total_count' => 50,
        ];

        $result = PaginatedResult::fromResponse($response, null, 10);

        $this->assertEquals(2, $result->count());
        $this->assertTrue($result->hasMore());
        $this->assertEquals(50, $result->getTotalCount());
        $this->assertEquals(10, $result->getLimit());
    }

    public function testFromResponseWithMapper(): void
    {
        $response = [
            'data' => [['id' => '1', 'name' => 'a'], ['id' => '2', 'name' => 'b']],
            'has_more' => false,
        ];

        $result = PaginatedResult::fromResponse($response, function ($item) {
            return $item['name'];
        });

        $this->assertEquals(['a', 'b'], $result->getData());
        $this->assertFalse($result->hasMore());
    }

    public function testFromResponseCamelCase(): void
    {
        $response = [
            'data' => [['id' => '1']],
            'hasMore' => true,
            'totalCount' => 25,
        ];

        $result = PaginatedResult::fromResponse($response);

        $this->assertTrue($result->hasMore());
        $this->assertEquals(25, $result->getTotalCount());
    }

    // ---- Integration: Cursor + PaginatedResult ----

    public function testCursorForNextPage(): void
    {
        $items = [['id' => 'a'], ['id' => 'b'], ['id' => 'c']];
        $result = new PaginatedResult($items, true, null, null, null, 3);

        $nextCursor = Cursor::forNextPage($result);

        $this->assertNotNull($nextCursor);
        $this->assertEquals('c', $nextCursor->getStartingAfter());
        $this->assertEquals(3, $nextCursor->getLimit());
    }

    public function testCursorForNextPageNoMore(): void
    {
        $items = [['id' => 'a'], ['id' => 'b']];
        $result = new PaginatedResult($items, false);

        $this->assertNull(Cursor::forNextPage($result));
    }

    public function testCursorForPreviousPage(): void
    {
        $items = [['id' => 'd'], ['id' => 'e'], ['id' => 'f']];
        $result = new PaginatedResult($items, true, null, null, null, 3);

        $prevCursor = Cursor::forPreviousPage($result);

        $this->assertNotNull($prevCursor);
        $this->assertEquals('d', $prevCursor->getEndingBefore());
        $this->assertEquals(3, $prevCursor->getLimit());
    }

    public function testCursorForPreviousPageEmpty(): void
    {
        $result = new PaginatedResult([]);

        $this->assertNull(Cursor::forPreviousPage($result));
    }
}
