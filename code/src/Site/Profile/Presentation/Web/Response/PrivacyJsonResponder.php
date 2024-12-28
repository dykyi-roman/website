<?php

declare(strict_types=1);

namespace Site\Profile\Presentation\Web\Response;

use Shared\Presentation\Responder\ResponderInterface;

final class PrivacyJsonResponder implements ResponderInterface
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

    public function error(string $message): self
    {
        $this->data = [
            'success' => false,
            'errors' => [
                'message' => $message,
            ],
        ];
        $this->statusCode = 500;

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