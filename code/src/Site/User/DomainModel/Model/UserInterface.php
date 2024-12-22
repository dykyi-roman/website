<?php

declare(strict_types=1);

namespace Site\User\DomainModel\Model;

use Shared\DomainModel\ValueObject\Email;
use Site\User\DomainModel\Enum\UserId;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

interface UserInterface extends \Symfony\Component\Security\Core\User\UserInterface, PasswordAuthenticatedUserInterface
{
    public function getId(): UserId;

    public function getEmail(): Email;

    public function isActive(): bool;

    public function isDeleted(): bool;

    public function updatePassword(string $hashedPassword): void;

    public function clearResetToken(): void;

    public function withReferral(string $referral): void;

    public function getName(): string;

    public function setToken(string $token): void;
}
