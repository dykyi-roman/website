<?php

declare(strict_types=1);

namespace Profile\UserStatus\DomainModel\Model;

use Doctrine\ORM\Mapping as ORM;
use Profile\UserStatus\DomainModel\Dto\UserUpdateStatus;
use Shared\DomainModel\ValueObject\UserId;

#[ORM\Entity]
#[ORM\Table(name: 'user_statuses')]
class UserStatus
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "bigint")]
    private ?int $id = null;

    #[ORM\Column(name: 'user_id', type: 'uuid')]
    private UserId $userId;

    #[ORM\Column(name: 'is_online', type: 'boolean')]
    private bool $isOnline = false;

    #[ORM\Column(name: 'last_online_at', type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $lastOnlineAt = null;

    public function __construct(UserId $userId, bool $isOnline, ?\DateTimeImmutable $lastOnlineAt = null)
    {
        $this->userId = $userId;
        $this->isOnline = $isOnline;
        $this->lastOnlineAt = $lastOnlineAt;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            new UserId($data['user_id']),
            (bool) $data['is_online'],
            new \DateTimeImmutable($data['last_online_at']),
        );
    }

    public function transformToUserUpdateStatus(): UserUpdateStatus
    {
        return new UserUpdateStatus($this->userId, $this->isOnline, $this->lastOnlineAt);
    }
}