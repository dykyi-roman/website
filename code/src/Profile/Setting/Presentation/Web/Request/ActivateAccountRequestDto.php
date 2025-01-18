<?php

declare(strict_types=1);

namespace Profile\Setting\Presentation\Web\Request;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class ActivateAccountRequestDto
{
    public function __construct(
        #[Assert\NotBlank]
        public int $status,
    ) {
    }
}
