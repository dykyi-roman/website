<?php

declare(strict_types=1);

namespace Site\User\DomainModel\Model;

use Shared\DomainModel\Model\DomainModelInterface;
use Shared\DomainModel\ValueObject\Email;
use Site\User\DomainModel\Enum\UserId;
use Site\User\DomainModel\Enum\UserStatus;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

interface UserInterface extends \Symfony\Component\Security\Core\User\UserInterface, PasswordAuthenticatedUserInterface, DomainModelInterface
{
    public function getId(): UserId;

    public function getEmail(): Email;

    public function getStatus(): UserStatus;

    public function isVerified(): bool;

    public function isActive(): bool;

    public function isDeleted(): bool;

    public function activate(): void;

    public function deactivate(): void;

    public function delete(): void;

    public function updatePassword(string $hashedPassword): void;

    public function clearResetToken(): void;

    public function withReferral(string $referral): void;

    public function getName(): string;

    public function setPasswordToken(string $token): void;

    public function setGoogleToken(?string $googleToken): void;

    public function setFacebookToken(?string $facebookToken): void;
}
