<?php

declare(strict_types=1);

namespace Profile\User\DomainModel\Repository;

use Profile\User\DomainModel\Enum\UserId;
use Profile\User\DomainModel\Model\UserInterface;
use Shared\DomainModel\ValueObject\Email;

interface UserRepositoryInterface
{
    public function save(UserInterface $user): void;

    /**
     * @throws \Symfony\Component\Security\Core\Exception\UserNotFoundException
     */
    public function findById(UserId $userId): UserInterface;

    public function findByEmail(Email $email): ?UserInterface;

    public function findByToken(string $field, string $token): ?UserInterface;
}
