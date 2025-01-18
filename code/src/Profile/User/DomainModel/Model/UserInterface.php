<?php

declare(strict_types=1);

namespace Profile\User\DomainModel\Model;

use Profile\User\DomainModel\Enum\UserId;
use Profile\User\DomainModel\Enum\UserStatus;
use Shared\DomainModel\Model\DomainModelInterface;
use Shared\DomainModel\ValueObject\Email;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

interface UserInterface extends \Symfony\Component\Security\Core\User\UserInterface, PasswordAuthenticatedUserInterface, DomainModelInterface
{
    public function id(): UserId;

    public function name(): string;

    public function email(): Email;

    public function status(): UserStatus;

    public function hasPassword(): bool;

    public function isVerified(): bool;

    public function isPhoneVerified(): bool;

    public function isEmailVerified(): bool;

    public function isActive(): bool;

    public function isDeleted(): bool;

    public function activate(): void;


    public function deactivate(): void;

    public function delete(): void;

    public function verifyEmail(): void;

    public function verifyPhone(): void;

    public function updatePassword(string $hashedPassword): void;

    public function clearResetToken(): void;

    public function withReferral(string $referral): void;

    public function setPasswordToken(string $token): void;

    public function setGoogleToken(?string $googleToken): void;

    public function setFacebookToken(?string $facebookToken): void;

    public function changeName(string $name): void;

    public function changeEmail(Email $email): void;

    public function changePhone(string $phone): void;

    public function getPhone(): ?string;

    public function changeAvatar(string $avatar): void;
}
