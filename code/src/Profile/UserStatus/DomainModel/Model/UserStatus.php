<?php

declare(strict_types=1);

namespace Profile\UserStatus\DomainModel\Model;

use Doctrine\ORM\Mapping as ORM;
use Shared\DomainModel\ValueObject\UserId;

#[ORM\Entity]
#[ORM\Table(name: 'user_statuses')]
class UserStatus
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private UserId $userId;

    #[ORM\Column(type: 'boolean')]
    private bool $isOnline = false;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $lastOnlineAt = null;

    public function markOnline(): void {
        $this->isOnline = true;
        $this->lastOnlineAt = new \DateTimeImmutable();
    }

    public function markOffline(): void {
        $this->isOnline = false;
    }
}