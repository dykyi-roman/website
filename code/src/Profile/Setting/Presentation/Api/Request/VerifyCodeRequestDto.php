<?php

declare(strict_types=1);

namespace Profile\Setting\Presentation\Api\Request;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class VerifyCodeRequestDto
{
    public function __construct(
        #[Assert\NotBlank(message: 'Type is required')]
        #[Assert\Choice(choices: ['email', 'phone'], message: 'Invalid verification type')]
        public string $type,

        #[Assert\NotBlank(message: 'Code is required')]
        #[Assert\Length(exactly: 6, exactMessage: 'Code must be exactly 6 characters')]
        #[Assert\Regex(pattern: '/^\d+$/', message: 'Code must contain only digits')]
        public string $code,
    ) {
    }
}
