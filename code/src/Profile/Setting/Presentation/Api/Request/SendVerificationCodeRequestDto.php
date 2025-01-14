<?php

declare(strict_types=1);

namespace Profile\Setting\Presentation\Api\Request;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class SendVerificationCodeRequestDto
{
    public function __construct(
        #[Assert\NotBlank(message: 'Type is required')]
        #[Assert\Choice(choices: ['email', 'phone'], message: 'Invalid verification type')]
        public string $type,
    ) {
    }
}
