<?php

declare(strict_types=1);

namespace App\Registration\Presentation\Web\Request;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class UserRegisterRequestDTO
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(min: 2, max: 100)]
        public string $name,

        #[Assert\NotBlank]
        #[Assert\Email]
        public string $email,

        #[Assert\NotBlank]
        #[Assert\Length(min: 8)]
        public string $password,

        #[Assert\NotBlank]
        public string $type,

        #[Assert\NotBlank]
        #[Assert\Length(max: 100)]
        public string $countryCode,

        #[Assert\Length(max: 20)]
        public ?string $phone = null,
    ) {
    }

    public function isPartner(): bool
    {
        return 'partner' === $this->type;
    }
}
