<?php

declare(strict_types=1);

namespace App\Registration\Presentation\Api\Response;

use App\Shared\Presentation\Responder\ResponderInterface;

final readonly class RegistrationCitiesResponse implements ResponderInterface
{
    public function __construct(
        private array $cities,
    ) {
    }

    public function respond(): self
    {
        return $this;
    }

    public function payload(): array
    {
        return [
            'cities' => $this->cities,
        ];
    }

    public function template(): string
    {
        return '';
    }

    public function statusCode(): int
    {
        return 200;
    }
}
