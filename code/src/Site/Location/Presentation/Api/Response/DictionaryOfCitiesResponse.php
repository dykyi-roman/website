<?php

declare(strict_types=1);

namespace Site\Location\Presentation\Api\Response;

use Shared\Presentation\Responder\ResponderInterface;

final readonly class DictionaryOfCitiesResponse implements ResponderInterface
{
    public function __construct(
        /** @var array<string, array> */
        private array $cities,
    ) {
    }

    public function respond(): self
    {
        return $this;
    }

    /** @return array<string, mixed> */
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
