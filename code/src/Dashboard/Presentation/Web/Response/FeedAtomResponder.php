<?php

declare(strict_types=1);

namespace App\Dashboard\Presentation\Web\Response;

use App\Shared\Presentation\Responder\TemplateResponderInterface;

final readonly class FeedAtomResponder implements TemplateResponderInterface
{
    private array $data;

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
