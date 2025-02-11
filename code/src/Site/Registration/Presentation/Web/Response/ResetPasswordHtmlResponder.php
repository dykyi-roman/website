<?php

declare(strict_types=1);

namespace Site\Registration\Presentation\Web\Response;

use Shared\Presentation\Responder\TemplateResponderInterface;

final class ResetPasswordHtmlResponder implements TemplateResponderInterface
{
    /** @var array<string, mixed> */
    private array $data = [];

    /** @param array<string, mixed> $data */
    public function context(array $data = []): self
    {
        $this->data = $data;

        return $this;
    }

    public function template(): string
    {
        return '@Registration/page/reset-password.html.twig';
    }

    /** @return array<string, mixed> */
    public function payload(): array
    {
        return $this->data;
    }

    public function respond(): self
    {
        return $this;
    }

    public function statusCode(): int
    {
        return 200;
    }

    public function headers(): array
    {
        return ['Content-Type' => 'text/html'];
    }
}
