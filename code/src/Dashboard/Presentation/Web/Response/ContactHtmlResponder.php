<?php

declare(strict_types=1);

namespace App\Dashboard\Presentation\Web\Response;

use App\Shared\Presentation\Responder\ResponderInterface;

final readonly class ContactHtmlResponder implements ResponderInterface
{
    private array $data;
    private const string TEMPLATE = '@Dashboard/page/contact.html.twig';

    public function __construct(
        private string $supportEmail,
        private string $supportPhone,
        private string $supportAddress,
        private string $supportMap,
    ) {
    }

    public function contacts(string $hours): array
    {
        return [
            'email' => $this->supportEmail,
            'phone' => $this->supportPhone,
            'address' => $this->supportAddress,
            'hours' => $hours,
            'map' => '<iframe src="https://maps.google.com/maps?width=100%25&amp;height=600&amp;hl=en&amp;q='.$this->supportMap.'&amp;t=&amp;z=14&amp;ie=UTF8&amp;iwloc=B&amp;output=embed" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>',
        ];
    }

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
