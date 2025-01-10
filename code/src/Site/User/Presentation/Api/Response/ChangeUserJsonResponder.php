<?php

declare(strict_types=1);

namespace Site\User\Presentation\Api\Response;

use Shared\Presentation\Responder\ResponderInterface;

final class ChangeUserJsonResponder implements ResponderInterface
{
    /** @var array<string, mixed> */
    private array $data = [];
    private int $statusCode;

    /** @return array<string, mixed> */
    public function payload(): array
    {
        return $this->data;
    }

    public function success(string $message): self
    {
        $this->data = [
            'success' => true,
            'message' => $message,
        ];
        $this->statusCode = 200;

        return $this;
    }

    public function respond(): self
    {
        return $this;
    }

    public function statusCode(): int
    {
        return $this->statusCode;
    }
}
