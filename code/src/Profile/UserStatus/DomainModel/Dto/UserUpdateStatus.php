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

    public static function fromArray(array $data): self
    {
        return new self(
            new UserId($data['user_id']),
            (bool)$data['is_online'],
            new \DateTimeImmutable($data['last_online_at']),
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'user_id' => $this->userId->toRfc4122(),
            'is_online' => $this->isOnline,
            'last_online_at' => $this->lastOnlineAt->format('c'),
        ];
    }
}