<?php

declare(strict_types=1);

namespace Site\Location\Presentation\Api\Request;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class DictionaryOfCitiesRequest
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(exactly: 2)]
        public string $countryCode,

        #[Assert\NotBlank]
        #[Assert\Length(min: 2, max: 5)]
        public string $lang,

        #[Assert\NotBlank]
        #[Assert\Length(min: 2)]
        public string $city,
    ) {
    }
}
