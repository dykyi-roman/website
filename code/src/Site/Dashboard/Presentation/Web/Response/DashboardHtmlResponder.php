<?php

declare(strict_types=1);

namespace Site\Dashboard\Presentation\Web\Response;

use Shared\Presentation\Responder\TemplateResponderInterface;

final readonly class DashboardHtmlResponder implements TemplateResponderInterface
{
    private array $data;

    public function context(array $data = []): self
    {
        $this->data = $data;

        return $this;
    }

    public function template(): string
    {
        return '@Dashboard/page/dashboard.html.twig';
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
