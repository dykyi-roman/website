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
    public function id(): UserId;

    public function name(): string;

    public function email(): Email;

    public function status(): UserStatus;

    public function isVerified(): bool;

    public function isActive(): bool;

    public function isDeleted(): bool;

    public function activate(): void;

    public function deactivate(): void;

    public function delete(): void;

    public function updatePassword(string $hashedPassword): void;

    public function clearResetToken(): void;

    public function withReferral(string $referral): void;

    public function setPasswordToken(string $token): void;

    public function setGoogleToken(?string $googleToken): void;

    public function setFacebookToken(?string $facebookToken): void;

    public function changeName(string $name): void;

    public function changeEmail(Email $email): void;

    public function changePhone(string $phone): void;

    public function changeAvatar(string $avatar): void;
}
