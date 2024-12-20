<?php

declare(strict_types=1);

namespace App\Registration\Presentation\Web\Response;

use App\Shared\Presentation\Responder\TemplateResponderInterface;

final readonly class ResetPasswordHtmlResponder implements TemplateResponderInterface
{
    private array $data;

    public function template(): string
    {
        return '@Registration/page/reset-password.html.twig';
    }

    public function payload(): array
    {
        return $this->data;
    }

    public function respond(array $data = []): self
    {
        $this->data = $data;

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
