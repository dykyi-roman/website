<?php

declare(strict_types=1);

namespace App\Registration\DomainModel\Repository;

use App\Shared\Domain\ValueObject\Email;
use Symfony\Component\Security\Core\User\UserInterface;

interface UserRepositoryInterface
{
    public function findByEmail(Email $email): ?UserInterface;

    public function isEmailUnique(Email $email): bool;

    public function save(UserInterface $user): void;
}
