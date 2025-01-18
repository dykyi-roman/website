<?php

declare(strict_types=1);

namespace Orders\Presentation\Api\Response;

use Shared\Presentation\Responder\ResponderInterface;

final class OrderSearchJsonResponder implements ResponderInterface
{
    /** @var array<string, mixed> */
    private array $data = [];
    private int $statusCode = 200;

    /**
     * @param array{
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
     * } $data
     */
    public function success(array $data, string $message): self
    {
        $this->data = [
            'success' => true,
            'message' => $message,
            'data' => $data,
        ];
        $this->statusCode = 200;

        return $this;
    }

    public function error(string $message, string $field = ''): self
    {
        $this->data = [
            'success' => false,
            'errors' => [
                'message' => $message,
            ],
        ];
        $this->statusCode = 400;

        return $this;
    }

    public function respond(): self
    {
        return $this;
    }

    /** @return array<string, mixed> */
    public function payload(): array
    {
        return $this->data;
    }

    public function statusCode(): int
    {
        return $this->statusCode;
    }
}
