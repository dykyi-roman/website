<?php

declare(strict_types=1);

namespace Notifications\Presentation\Web\Request;

use Symfony\Component\Validator\Constraints as Assert;

final class NotificationsRequestDto
{
    #[Assert\Type('integer')]
    #[Assert\PositiveOrZero]
    public int $page = 1;

    #[Assert\Type('integer')]
    #[Assert\Range(min: 1, max: 100)]
    public int $limit = 20;
}
