<?php

declare(strict_types=1);

namespace App\Registration\Presentation\Web\Responder;

use App\Shared\Presentation\Responder\ResponderInterface;

final class RegistrationJsonResponder implements ResponderInterface
{
    private array $data = [];
    private int $statusCode = 200;

    public function success(string $message): self
    {
        $this->data = [
            'success' => true,
            'message' => $message,
        ];
        $this->statusCode = 201;

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

    public function error(\Throwable $exception): self
    {
        $this->data = [
            'success' => false,
            'errors' => [
                'message' => $exception->getMessage(),
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

    public function template(): string
    {
        return '';
    }
}
