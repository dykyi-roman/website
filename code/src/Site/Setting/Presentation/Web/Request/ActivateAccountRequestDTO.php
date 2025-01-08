<?php

declare(strict_types=1);

namespace Site\Setting\Presentation\Web\Request;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class ActivateAccountRequestDTO
{
    public function __construct(
        #[Assert\NotBlank]
        public int $status,
    ) {
    }
}
