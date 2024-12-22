<?php

declare(strict_types=1);

namespace Site\User\DomainModel\Repository;

use Shared\DomainModel\ValueObject\Email;
use Site\User\DomainModel\Enum\UserId;
use Site\User\DomainModel\Model\UserInterface;

interface UserRepositoryInterface
{
    public function save(UserInterface $user): void;

    public function findById(UserId $id): ?UserInterface;

    public function findByEmail(Email $email): ?UserInterface;

    public function findByToken(string $token): ?UserInterface;

    public function isEmailUnique(Email $email): bool;
}
