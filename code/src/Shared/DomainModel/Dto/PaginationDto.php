<?php

declare(strict_types=1);

namespace Shared\DomainModel\Dto;

/**
 * @template T
 */
final readonly class PaginationDto implements \JsonSerializable
{
    /**
     * @param array<array-key, T> $items
     */
    public function __construct(
        public array $items,
        public int $page,
        public int $limit,
    ) {
    }

    /** @return array<string, mixed> */
    public function jsonSerialize(): array
    {
        $offset = ($this->page - 1) * $this->limit;
        $paginatedItems = array_slice($this->items, $offset, $this->limit);

        return [
            'items' => $paginatedItems,
            'total' => count($this->items),
            'page' => $this->page,
            'limit' => $this->limit,
            'total_pages' => (int) ceil(count($this->items) / $this->limit),
        ];
    }
}
