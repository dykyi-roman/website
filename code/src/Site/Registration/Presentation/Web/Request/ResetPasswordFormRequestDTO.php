<?php

declare(strict_types=1);

namespace Site\Registration\Presentation\Web\Request;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class ResetPasswordFormRequestDTO
{
    public function __construct(
        #[Assert\NotBlank]
        public string $token,
    ) {
    }
}
