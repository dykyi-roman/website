<?php

declare(strict_types=1);

namespace Site\Registration\Presentation\Api\Request;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class ChangePasswordRequestDto
{
    public function __construct(
        #[Assert\NotBlank(message: 'Old password is required')]
        public string $currentPassword,

        #[Assert\NotBlank(message: 'New password is required')]
        #[Assert\Length(
            min: 8,
            minMessage: 'Password must be at least {{ limit }} characters long'
        )]
        public string $newPassword,
    ) {
    }
}
