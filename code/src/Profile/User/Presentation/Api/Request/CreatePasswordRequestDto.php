<?php

declare(strict_types=1);

namespace Profile\User\Presentation\Api\Request;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class CreatePasswordRequestDto
{
    public function __construct(
        #[Assert\NotBlank(message: 'Password is required')]
        public string $password,

        #[Assert\NotBlank(message: 'Confirmation password is required')]
        public string $confirmationPassword,
    ) {
    }
}
