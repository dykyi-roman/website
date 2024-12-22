<?php

declare(strict_types=1);

namespace Site\Dashboard\Presentation\Web\Response;

use Shared\Presentation\Responder\TemplateResponderInterface;

final class FeedAtomHtmlResponder implements TemplateResponderInterface
{
    /** @var array<string, mixed> */
    private array $data = [];

    public function context(array $data = []): self
    {
        $this->data = $data;

        return $this;
    }

    public function template(): string
    {
        return '@Dashboard/feed/atom.xml.twig';
    }

    public function headers(): array
    {
        return ['Content-Type' => 'application/atom+xml; charset=UTF-8'];
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
}
