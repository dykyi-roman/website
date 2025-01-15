<?php

declare(strict_types=1);

namespace Site\Registration\Tests\Unit\DomainModel\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Profile\User\DomainModel\Enum\UserId;
use Profile\User\DomainModel\Model\User;
use Profile\User\DomainModel\Repository\UserRepositoryInterface;
use Psr\Log\LoggerInterface;
use Shared\DomainModel\ValueObject\Email;
use Site\Registration\DomainModel\Exception\InvalidPasswordException;
use Site\Registration\DomainModel\Exception\TokenExpiredException;
use Site\Registration\DomainModel\Service\ResetPasswordService;
use Site\Registration\DomainModel\Service\TokenGeneratorInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[CoversClass(ResetPasswordService::class)]
final class ResetPasswordServiceTest extends TestCase
{
    private UserRepositoryInterface $userRepository;
    private TokenGeneratorInterface $tokenGenerator;
    private UserPasswordHasherInterface $passwordHasher;
    private LoggerInterface $logger;
    private ResetPasswordService $service;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->tokenGenerator = $this->createMock(TokenGeneratorInterface::class);
        $this->passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->service = new ResetPasswordService(
            $this->userRepository,
            $this->tokenGenerator,
            $this->passwordHasher,
            $this->logger
        );
    }

    public function testSuccessfulPasswordReset(): void
    {
        $token = 'valid-token';
        $newPassword = 'NewValidPass123';
        $hashedPassword = 'hashed-password';
        $userId = new UserId();
        $email = Email::fromString('test@example.com');

        $user = $this->createMock(User::class);
        $user->expects($this->once())
            ->method('isDeleted')
            ->willReturn(false);
        $user->expects($this->once())
            ->method('isActive')
            ->willReturn(true);
        $user->expects($this->once())
            ->method('id')
            ->willReturn($userId);
        $user->expects($this->once())
            ->method('email')
            ->willReturn($email);
        $user->expects($this->once())
            ->method('updatePassword')
            ->with($hashedPassword);
        $user->expects($this->once())
            ->method('clearResetToken');

        $this->tokenGenerator->expects($this->once())
            ->method('isValid')
            ->with($token)
            ->willReturn(true);

        $this->userRepository->expects($this->once())
            ->method('findByToken')
            ->with('passwordToken', $token)
            ->willReturn($user);

        $this->passwordHasher->expects($this->once())
            ->method('hashPassword')
            ->with($user, $newPassword)
            ->willReturn($hashedPassword);

        $this->userRepository->expects($this->once())
            ->method('save')
            ->with($user);

        $this->logger->expects($this->once())
            ->method('info')
            ->with(
                'Password reset successful',
                [
                    'user_id' => $userId->toRfc4122(),
                    'email' => (string) $email,
                ]
            );

        $this->service->reset($token, $newPassword);
    }

    public function testResetWithInvalidToken(): void
    {
        $this->tokenGenerator->expects($this->once())
            ->method('isValid')
            ->with('invalid-token')
            ->willReturn(false);

        $this->expectException(TokenExpiredException::class);
        $this->expectExceptionMessage('Invalid or expired reset token');

        $this->service->reset('invalid-token', 'NewPass123');
    }

    public function testResetWithUserNotFound(): void
    {
        $token = 'valid-token';

        $this->tokenGenerator->expects($this->once())
            ->method('isValid')
            ->with($token)
            ->willReturn(true);

        $this->userRepository->expects($this->once())
            ->method('findByToken')
            ->with('passwordToken', $token)
            ->willReturn(null);

        $this->expectException(TokenExpiredException::class);
        $this->expectExceptionMessage('No user found for this reset token');

        $this->service->reset($token, 'NewPass123');
    }

    public function testResetWithTooShortPassword(): void
    {
        $token = 'valid-token';
        $user = $this->createMock(User::class);
        $user->method('isDeleted')->willReturn(false);
        $user->method('isActive')->willReturn(true);

        $this->tokenGenerator->expects($this->once())
            ->method('isValid')
            ->with($token)
            ->willReturn(true);

        $this->userRepository->expects($this->once())
            ->method('findByToken')
            ->with('passwordToken', $token)
            ->willReturn($user);

        $this->expectException(InvalidPasswordException::class);
        $this->expectExceptionMessage('Password must be at least 8 characters long');

        $this->service->reset($token, 'short');
    }

    public function testResetWithPasswordWithoutLetters(): void
    {
        $token = 'valid-token';
        $user = $this->createMock(User::class);
        $user->method('isDeleted')->willReturn(false);
        $user->method('isActive')->willReturn(true);

        $this->tokenGenerator->expects($this->once())
            ->method('isValid')
            ->with($token)
            ->willReturn(true);

        $this->userRepository->expects($this->once())
            ->method('findByToken')
            ->with('passwordToken', $token)
            ->willReturn($user);

        $this->expectException(InvalidPasswordException::class);
        $this->expectExceptionMessage('Password must contain at least one English letter');

        $this->service->reset($token, '12345678');
    }
}
