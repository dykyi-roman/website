<?php

declare(strict_types=1);

namespace Profile\User\Tests\Unit\Application\ChangePassword\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Profile\User\Application\ChangeUserPassword\Service\UserPasswordHasher;
use Profile\User\DomainModel\Model\UserInterface;
use Profile\User\DomainModel\Repository\UserRepositoryInterface;
use Profile\User\DomainModel\Enum\UserId;
use Psr\Log\LoggerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use RuntimeException;

#[CoversClass(UserPasswordHasher::class)]
final class UserPasswordHasherTest extends TestCase
{
    private UserPasswordHasherInterface&MockObject $passwordHasher;
    private UserRepositoryInterface&MockObject $userRepository;
    private LoggerInterface&MockObject $logger;
    private UserInterface&MockObject $user;
    private UserPasswordHasher $service;

    protected function setUp(): void
    {
        $this->passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->user = $this->createMock(UserInterface::class);

        $this->service = new UserPasswordHasher(
            $this->passwordHasher,
            $this->userRepository,
            $this->logger
        );
    }

    public static function passwordValidationProvider(): array
    {
        return [
            'valid password' => ['valid-password', true],
            'invalid password' => ['invalid-password', false],
        ];
    }

    #[Test]
    #[DataProvider('passwordValidationProvider')]
    public function validatePasswordShouldReturnExpectedResult(string $password, bool $expected): void
    {
        $this->passwordHasher
            ->expects($this->once())
            ->method('isPasswordValid')
            ->with($this->user, $password)
            ->willReturn($expected);

        $result = $this->service->isValid($this->user, $password);
        
        $this->assertSame($expected, $result);
    }

    #[Test]
    public function changePasswordShouldUpdateAndSaveUserPassword(): void
    {
        $password = 'new-password';
        $hashedPassword = 'hashed-password';

        $this->passwordHasher
            ->expects($this->once())
            ->method('hashPassword')
            ->with($this->user, $password)
            ->willReturn($hashedPassword);

        $this->user
            ->expects($this->once())
            ->method('updatePassword')
            ->with($hashedPassword);

        $this->userRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->user);

        $this->logger
            ->expects($this->never())
            ->method('error');

        $this->service->change($this->user, $password);
    }

    #[Test]
    public function changePasswordShouldLogErrorWhenSaveFails(): void
    {
        $password = 'new-password';
        $hashedPassword = 'hashed-password';
        $userId = new UserId('00000000-0000-0000-0000-000000000001');
        $exception = new RuntimeException('Database error');

        $this->user
            ->expects($this->once())
            ->method('id')
            ->willReturn($userId);

        $this->passwordHasher
            ->expects($this->once())
            ->method('hashPassword')
            ->with($this->user, $password)
            ->willReturn($hashedPassword);

        $this->user
            ->expects($this->once())
            ->method('updatePassword')
            ->with($hashedPassword);

        $this->userRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->user)
            ->willThrowException($exception);

        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with(
                'Password change failed',
                [
                    'userId' => $userId->toRfc4122(),
                    'password' => $password,
                    'error' => 'Database error',
                ]
            );

        $this->service->change($this->user, $password);
    }
}
