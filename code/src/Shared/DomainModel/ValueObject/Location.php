<?php

declare(strict_types=1);

namespace App\Shared\Domain\ValueObject;

use InvalidArgumentException;

final readonly class Location implements \JsonSerializable
{
    public function __construct(
        public Country $country,
        public ?City $city = null,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if (!$this->country instanceof Country) {
            throw new InvalidArgumentException('Invalid country object provided');
        }

        if ($this->city !== null && !$this->city instanceof City) {
            throw new InvalidArgumentException('Invalid city object provided');
        }
    }

    public function jsonSerialize(): array
    {
        return [
            'country' => $this->country->jsonSerialize(),
            'city' => $this->city?->jsonSerialize(),
        ];
    }
}