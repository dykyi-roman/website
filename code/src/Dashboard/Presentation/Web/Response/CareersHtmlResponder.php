<?php

declare(strict_types=1);

namespace App\Dashboard\Presentation\Web\Response;

use App\Shared\Presentation\Responder\TemplateResponderInterface;

final readonly class CareersHtmlResponder implements TemplateResponderInterface
{
    private array $data;

    public function template(): string
    {
        return '@Dashboard/page/careers.html.twig';
    }

    public function payload(): array
    {
        return $this->data;
    }

    public function context(array $data = []): self
    {
        $this->data = $data;

        return $this;
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
