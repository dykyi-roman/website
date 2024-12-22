<?php

declare(strict_types=1);

namespace Site\Profile\Presentation\Web\Response;

use Shared\Presentation\Responder\TemplateResponderInterface;

final readonly class ProfileHtmlResponder implements TemplateResponderInterface
{
    private array $data;

    public function template(): string
    {
        return '@Profile/page/profile.html.twig';
    }

    /** @return array<string, mixed> */
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
