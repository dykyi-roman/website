<?php

declare(strict_types=1);

namespace Site\Registration\Presentation\Web\Request;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class ResetPasswordRequestDto
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(min: 8)]
        public string $password,

        #[Assert\NotBlank]
        #[Assert\Length(min: 8)]
        public string $confirmPassword,

        #[Assert\NotBlank]
        public string $token,
    ) {
    }
}
