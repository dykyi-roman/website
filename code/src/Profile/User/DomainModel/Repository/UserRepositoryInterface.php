<?php

declare(strict_types=1);

namespace Profile\User\DomainModel\Repository;

use Profile\User\DomainModel\Model\UserInterface;
use Shared\DomainModel\ValueObject\Email;
use Shared\DomainModel\ValueObject\UserId;

interface UserRepositoryInterface
{
    public function save(UserInterface $user): void;

    /**
     * @return UserId[]
     */
    public function findAll(): array;

    /**
     * @throws \Profile\User\DomainModel\Exception\UserNotFoundException
     */
    public function findById(UserId $userId): UserInterface;

    public function findByEmail(Email $email): ?UserInterface;

    public function findByToken(string $field, string $token): ?UserInterface;
}
