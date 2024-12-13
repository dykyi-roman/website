<?php

declare(strict_types=1);

namespace App\Registration\DomainModel\Service;

use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

final readonly class AuthenticationService
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function verifyPassword(PasswordAuthenticatedUserInterface $user, string $password): bool
    {
        return $this->passwordHasher->isPasswordValid($user, $password);
    }

    public function hashPassword(PasswordAuthenticatedUserInterface $user, string $password): string
    {
        return $this->passwordHasher->hashPassword($user, $password);
    }
}
