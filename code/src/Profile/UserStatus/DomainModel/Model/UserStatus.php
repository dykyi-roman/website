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
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'bigint')]
    private int $id; // @phpstan-ignore-line

    #[ORM\Column(name: 'user_id', type: 'uuid')]
    private UserId $userId;

    #[ORM\Column(name: 'is_online', type: 'boolean')]
    private bool $isOnline = false;

    #[ORM\Column(name: 'last_online_at', type: 'datetime_immutable')]
    private \DateTimeImmutable $lastOnlineAt;

    public function __construct(UserId $userId, bool $isOnline, \DateTimeImmutable $lastOnlineAt)
    {
        $this->userId = $userId;
        $this->isOnline = $isOnline;
        $this->lastOnlineAt = $lastOnlineAt;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $userId = is_string($data['user_id']) ? $data['user_id'] : throw new \InvalidArgumentException('user_id must be a string');
        $lastOnlineAt = isset($data['last_online_at']) && is_string($data['last_online_at'])
            ? new \DateTimeImmutable($data['last_online_at'])
            : new \DateTimeImmutable();

        return new self(
            new UserId($userId),
            (bool) $data['is_online'],
            $lastOnlineAt,
        );
    }

    public function getUserId(): UserId
    {
        return $this->userId;
    }

    public function isOnline(): bool
    {
        return $this->isOnline;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getLastOnlineAt(): \DateTimeImmutable
    {
        return $this->lastOnlineAt;
    }

    public function updateStatus(bool $isOnline, \DateTimeImmutable $lastActivityAt): void
    {
        $this->isOnline = $isOnline;
        $this->lastOnlineAt = $lastActivityAt;
    }
}
