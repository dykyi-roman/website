<?php

declare(strict_types=1);

namespace Site\Dashboard\Presentation\Web\Response;

use Shared\Presentation\Responder\TemplateResponderInterface;

final class ContactHtmlResponder implements TemplateResponderInterface
{
    private array $data;

    public function __construct(
        private readonly string $supportEmail,
        private readonly string $supportPhone,
        private readonly string $supportAddress,
        private readonly string $supportMap,
    ) {
    }

    public function contacts(string $hours): self
    {
        $this->data['contact'] = [
            'email' => $this->supportEmail,
            'phone' => $this->supportPhone,
            'address' => $this->supportAddress,
            'hours' => $hours,
            'map' => '<iframe src="https://maps.google.com/maps?width=100%25&amp;height=600&amp;hl=en&amp;q='.$this->supportMap.'&amp;t=&amp;z=14&amp;ie=UTF8&amp;iwloc=B&amp;output=embed" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>',
        ];

        return $this;
    }

    public function template(): string
    {
        return '@Dashboard/page/contact.html.twig';
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
