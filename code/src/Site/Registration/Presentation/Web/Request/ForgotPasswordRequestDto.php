<?php

declare(strict_types=1);

namespace Site\Registration\Presentation\Web\Request;

use Shared\DomainModel\ValueObject\Email;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class ForgotPasswordRequestDto
{
    public function __construct(
        #[Assert\NotBlank(message: 'Email is required')]
        #[Assert\Email]
        private string $email,
    ) {
    }

    public function email(): Email
    {
        return Email::fromString($this->email);
    }
}
