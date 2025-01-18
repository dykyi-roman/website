<?php

declare(strict_types=1);

namespace Profile\User\Presentation\Api\Request;

use Profile\User\DomainModel\Enum\UserStatus;
use Symfony\Component\Validator\Constraints as Assert;

final class ChangeUserStatusDto
{
    #[Assert\NotBlank(message: 'Status is required')]
    private int $status;

    public function status(): UserStatus
    {
        return UserStatus::from($this->status);
    }
}
