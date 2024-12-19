<?php

declare(strict_types=1);

namespace App\Location\Presentation\Api\Response;

use App\Shared\Presentation\Responder\ResponderInterface;

final readonly class DictionaryOfCitiesResponse implements ResponderInterface
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

    public function statusCode(): int
    {
        return 200;
    }
}
