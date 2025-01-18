<?php

declare(strict_types=1);

namespace Notification\Presentation\Api\Response;

use Shared\Presentation\Responder\ResponderInterface;

final class NotificationResponder implements ResponderInterface
{
    /** @var array<string, mixed> */
    private array $data = [];
    private int $statusCode = 200;

    public function success(\JsonSerializable|array $data, string $message): self
    {
        $this->data = [
            'success' => true,
            'message' => $message,
            'data' => is_array($data) ? $data : $data->jsonSerialize(),
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