<?php

declare(strict_types=1);

namespace App\Registration\Presentation\Web\Request;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class ResetPasswordRequestDTO
{
    public function __construct(
        #[Assert\NotBlank(message: 'Token is required')]
        public string $token,
    ) {
    }
}
