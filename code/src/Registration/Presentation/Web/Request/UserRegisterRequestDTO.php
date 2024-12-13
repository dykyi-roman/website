<?php

declare(strict_types=1);

namespace App\Registration\Presentation\Web\Request;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;

#[MapRequestPayload]
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

        #[Assert\Length(max: 20)]
        public ?string $phone = null,

        #[Assert\Length(max: 100)]
        public ?string $country = null,

        #[Assert\Length(max: 255)]
        public ?string $city = null,
    ) {
    }

    public function isPartner(): bool
    {
        return $this->type === 'partner';
    }
}
