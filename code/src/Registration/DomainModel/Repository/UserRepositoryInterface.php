<?php

declare(strict_types=1);

namespace App\Registration\DomainModel\Repository;

use App\Shared\DomainModel\ValueObject\Email;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Uuid;

interface UserRepositoryInterface
{
    public function findByToken(string $token): ?UserInterface;

    public function findByEmail(Email $email): ?UserInterface;

    public function isEmailUnique(Email $email): bool;

    public function save(UserInterface $user): void;
}
