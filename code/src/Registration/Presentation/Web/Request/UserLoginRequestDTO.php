<?php

declare(strict_types=1);

namespace App\Registration\Presentation\Web\Request;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class UserLoginRequestDTO
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Email]
        public string $email,

        #[Assert\NotBlank]
        #[Assert\Length(min: 8)]
        public string $password,
    ) {
    }
}
