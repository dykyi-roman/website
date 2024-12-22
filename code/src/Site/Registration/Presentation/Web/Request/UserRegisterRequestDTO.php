<?php

declare(strict_types=1);

namespace Site\Registration\Presentation\Web\Request;

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
        #[Assert\Length(max: 100)]
        public string $countryCode,

        #[Assert\Length(max: 20)]
        public ?string $phone = null,

        #[Assert\NotBlank(message: 'The reCAPTCHA is required', groups: ['captcha'])]
        public ?string $g_recaptcha_response = null,
    ) {
    }
}
