<?php

declare(strict_types=1);

namespace Profile\Setting\Presentation\Api\Request;

use Symfony\Component\Validator\Constraints as Assert;

final class VerifyCodeRequestDto
{
    #[Assert\NotBlank]
    #[Assert\Length(exactly: 6)]
    public string $code;
}
