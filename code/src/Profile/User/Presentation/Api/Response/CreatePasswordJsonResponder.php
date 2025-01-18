<?php

declare(strict_types=1);

namespace Profile\User\Presentation\Api\Response;

use Shared\Presentation\Responder\ResponderInterface;

final class CreatePasswordJsonResponder implements ResponderInterface
{
    /** @var array<string, mixed> */
    private array $data = [];
    private int $statusCode = 200;

    public function success(string $message): self
    {
        $this->data = [
            'success' => true,
            'message' => $message,
        ];
        $this->statusCode = 200;

        return $this;
    }

    public function validationError(string $message, string $field = ''): self
    {
        $this->data = [
            'success' => false,
            'errors' => [
                'message' => $message,
                'field' => $field,
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
