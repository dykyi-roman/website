<?php

declare(strict_types=1);

namespace Site\Registration\DomainModel\Service;

use Profile\User\DomainModel\Repository\UserRepositoryInterface;
use Psr\Log\LoggerInterface;
use Site\Registration\DomainModel\Exception\InvalidPasswordException;
use Site\Registration\DomainModel\Exception\TokenExpiredException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ResetPasswordService
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private TokenGeneratorInterface $tokenGenerator,
        private UserPasswordHasherInterface $passwordHasher,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @throws TokenExpiredException    If the reset token is invalid or expired
     * @throws InvalidPasswordException If the password does not meet complexity requirements
     */
    public function reset(string $token, string $newPassword): void
    {
        if (!$this->tokenGenerator->isValid($token)) {
            throw new TokenExpiredException('Invalid or expired reset token');
        }

        $user = $this->userRepository->findByToken('passwordToken', $token);
        if (!$user || $user->isDeleted() || !$user->isActive()) {
            throw new TokenExpiredException('No user found for this reset token');
        }

        $this->validatePasswordComplexity($newPassword);
        $hashedPassword = $this->passwordHasher->hashPassword($user, $newPassword);

        $user->updatePassword($hashedPassword);
        $user->clearResetToken();
        $this->userRepository->save($user);

        $this->logger->info('Password reset successful', [
            'user_id' => $user->id(),
            'email' => $user->email(),
        ]);
    }

    /**
     * @throws InvalidPasswordException If password does not meet requirements
     */
    private function validatePasswordComplexity(string $password): void
    {
        if (strlen($password) < 8) {
            throw new InvalidPasswordException('Password must be at least 8 characters long');
        }

        if (!preg_match('/[a-zA-Z]/', $password)) {
            throw new InvalidPasswordException('Password must contain at least one English letter');
        }
    }
}
