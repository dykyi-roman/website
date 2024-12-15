<?php

declare(strict_types=1);

namespace App\Dashboard\Presentation\Web\Response;

use App\Shared\Presentation\Responder\ResponderInterface;

final readonly class PrivacyHtmlResponder implements ResponderInterface
{
    private array $data;
    private const string TEMPLATE = '@Dashboard/page/privacy.html.twig';

    public function template(): string
    {
        return self::TEMPLATE;
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
}
