<?php

declare(strict_types=1);

namespace Site\Location\Presentation\Api\Request;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class DetectLocationRequest
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Regex(pattern: '/^[-]?((([0-8]?[0-9])(\.[0-9]+)?)|90(\.0+)?)$/')]
        public string $latitude,

        #[Assert\NotBlank]
        #[Assert\Regex(pattern: '/^[-]?((([0-9]?[0-9]|1[0-7][0-9])(\.[0-9]+)?)|180(\.0+)?)$/')]
        public string $longitude,
    ) {
    }
}
