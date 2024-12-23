<?php

declare(strict_types=1);

namespace Services\DomainModel\Service;

use Services\DomainModel\Enum\OrderType;

interface ServicesInterface
{
    /**
     * @return array{
     *     items: array<int, array{
     *         id: int,
     *         title: string,
     *         description: string,
     *         category: string,
     *         url: string,
     *         feedback_count: string,
     *         image_url: string,
     *         features: array<int, string>,
     *         rating: int,
     *         review_count: int,
     *         price: string
     *     }>,
     *     total: int,
     *     page: int,
     *     limit: int,
     *     total_pages: int
     * }
     */
    public function search(
        string $query,
        OrderType $order,
        int $page,
        int $limit,
    ): array;

    /**
     * @return array<int, array{
     *     id: int,
     *     title: string,
     *     description: string,
     *     category: string,
     *     url: string,
     *     feedback_count: string,
     *     image_url: string,
     *     features: array<int, string>,
     *     rating: int,
     *     review_count: int,
     *     price: string
     * }>
     */
    public function last(int $count): array;
}
