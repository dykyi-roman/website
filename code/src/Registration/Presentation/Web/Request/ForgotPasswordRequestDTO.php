<?php

declare(strict_types=1);

namespace App\Registration\Presentation\Web\Request;

use App\Shared\DomainModel\ValueObject\Email;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class ForgotPasswordRequestDTO
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
