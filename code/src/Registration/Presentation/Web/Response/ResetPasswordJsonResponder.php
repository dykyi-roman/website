<?php

declare(strict_types=1);

namespace App\Registration\Presentation\Web\Response;

use App\Shared\Presentation\Responder\ResponderInterface;

final class ResetPasswordJsonResponder implements ResponderInterface
{
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

    public function payload(): array
    {
        return $this->data;
    }

    public function statusCode(): int
    {
        return $this->statusCode;
    }
}
