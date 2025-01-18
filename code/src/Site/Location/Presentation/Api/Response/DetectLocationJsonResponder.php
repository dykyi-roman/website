<?php

declare(strict_types=1);

namespace Site\Location\Presentation\Api\Response;

use Shared\DomainModel\ValueObject\Location;
use Shared\Presentation\Responder\ResponderInterface;

final readonly class DetectLocationJsonResponder implements ResponderInterface
{
    public function __construct(
        private Location $location,
    ) {
    }

    public function respond(): ResponderInterface
    {
        return $this;
    }

    /**
     * @return array{country: array{code: string}|null, city: array{name: string, transcription: string, address: string|null}|null}
     */
    public function payload(): array
    {
        return $this->location->jsonSerialize();
    }

    public function statusCode(): int
    {
        return 200;
    }
}
