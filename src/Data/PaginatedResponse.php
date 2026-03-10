<?php

declare(strict_types=1);

namespace Nusii\Data;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * @implements IteratorAggregate<int, array>
 */
readonly class PaginatedResponse implements Countable, IteratorAggregate
{
    public function __construct(
        public array $data,
        public int $currentPage,
        public ?int $nextPage,
        public ?int $prevPage,
        public int $totalPages,
        public int $totalCount,
    ) {}

    public function count(): int
    {
        return count($this->data);
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->data);
    }

    public function hasNextPage(): bool
    {
        return $this->nextPage !== null;
    }

    public function hasPrevPage(): bool
    {
        return $this->prevPage !== null;
    }
}
