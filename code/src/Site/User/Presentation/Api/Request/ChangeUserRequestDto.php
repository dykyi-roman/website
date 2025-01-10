<?php

declare(strict_types=1);

namespace Site\User\Presentation\Api\Request;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class ChangeUserRequestDto
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Regex(pattern: '/^[a-zA-Z\s\'-]{2,100}$/', message: 'Invalid name format')]
        public string $name,

        #[Assert\NotBlank]
        #[Assert\Email]
        public string $email,

        #[Assert\NotBlank]
        #[Assert\Regex(pattern: '/^\+\d{10,15}$/', message: 'Invalid phone number format')]
        public string $phone,

        #[Assert\Image(
            maxSize: '5M',
            mimeTypes: ['image/jpeg', 'image/png'],
            mimeTypesMessage: 'Please upload a valid image (JPEG or PNG)'
        )]
        public ?string $avatar = null,
    ) {
    }
}