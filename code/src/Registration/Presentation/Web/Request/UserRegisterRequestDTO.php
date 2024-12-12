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
        #[Assert\Length(min: 2, max: 255)]
        public string $name,

        #[Assert\NotBlank]
        #[Assert\Email]
        public string $email,

        #[Assert\NotBlank]
        #[Assert\Length(min: 6)]
        public string $password,

        #[Assert\Length(max: 20)]
        public ?string $phone = null,

        #[Assert\Length(max: 100)]
        public ?string $country = null,

        #[Assert\Length(max: 100)]
        public ?string $city = null,

        public ?string $type = null,
    ) {
    }

    public function isPartner(): bool
    {
        return $this->type === 'partner';
    }
}
