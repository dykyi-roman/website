<?php

declare(strict_types=1);

namespace Profile\UserStatus\DomainModel\Dto;

use Shared\DomainModel\ValueObject\UserId;

final readonly class UserUpdateStatus implements \JsonSerializable
{
    public function __construct(
        public UserId $userId,
        public bool $isOnline,
        public \DateTimeImmutable $lastOnlineAt,
    ) {
    }

    public static function createOnline(UserId $userId): self
    {
        return new self(
            $userId,
            true,
            new \DateTimeImmutable(),
        );
    }

    /**
     * @param array<mixed, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $userId = is_string($data['user_id']) ? $data['user_id'] : throw new \InvalidArgumentException('user_id must be a string');
        $lastOnlineAt = is_string($data['last_online_at']) ? $data['last_online_at'] : throw new \InvalidArgumentException('last_online_at must be a string');

        return new self(
            new UserId($userId),
            (bool) $data['is_online'],
            new \DateTimeImmutable($lastOnlineAt),
        );
    }

    /**
     * @return array<string, string|bool>
     */
    public function jsonSerialize(): array
    {
        return [
            'user_id' => $this->userId->toRfc4122(),
            'is_online' => $this->isOnline,
            'last_online_at' => $this->lastOnlineAt->format('c'),
        ];
    }
}
