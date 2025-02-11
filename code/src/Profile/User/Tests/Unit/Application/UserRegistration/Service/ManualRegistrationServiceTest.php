<?php

declare(strict_types=1);

namespace Profile\User\Tests\Unit\Application\UserRegistration\Service;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Profile\User\Application\UserRegistration\Service\ManualRegistrationService;
use Profile\User\DomainModel\Model\UserInterface;
use Profile\User\DomainModel\Repository\UserRepositoryInterface;
use Shared\DomainModel\ValueObject\City;
use Shared\DomainModel\ValueObject\Country;
use Shared\DomainModel\ValueObject\Email;
use Shared\DomainModel\ValueObject\Location;
use Site\Registration\DomainModel\Service\ReferralReceiverInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class ManualRegistrationServiceTest extends TestCase
{
    private MockObject&UserPasswordHasherInterface $passwordHasher;
    private MockObject&ReferralReceiverInterface $referralReceiver;
    private MockObject&UserRepositoryInterface $userRepository;
    private ManualRegistrationService $service;

    protected function setUp(): void
    {
        $this->passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $this->referralReceiver = $this->createMock(ReferralReceiverInterface::class);
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->service = new ManualRegistrationService(
            $this->passwordHasher,
            $this->referralReceiver,
            $this->userRepository,
        );
    }

    public function testCreateUserSuccessfully(): void
    {
        // Arrange
        $name = 'John Doe';
        $email = Email::fromString('john@example.com');
        $location = new Location(
            new Country('US'),
            new City('New York', 'New York', '10001')
        );
        $phone = '+1234567890';
        $password = 'password123';
        $hashedPassword = 'hashed_password';
        $referral = 'REF123';

        $this->passwordHasher
            ->expects($this->once())
            ->method('hashPassword')
            ->willReturn($hashedPassword);

        $this->referralReceiver
            ->expects($this->once())
            ->method('referral')
            ->willReturn($referral);

        $this->userRepository
            ->expects($this->once())
            ->method('save');

        // Act
        $user = $this->service->createUser($name, $email, $location, $phone, $password);

        // Assert
        $this->assertInstanceOf(UserInterface::class, $user);
        $this->assertEquals($name, $user->name());
        $this->assertEquals($email, $user->email());
        $this->assertEquals($phone, $user->getPhone());
    }

    public function testCreateUserWithoutPhone(): void
    {
        // Arrange
        $name = 'John Doe';
        $email = Email::fromString('john@example.com');
        $location = new Location(
            new Country('US'),
            new City('New York', 'New York', '10001')
        );
        $password = 'password123';
        $hashedPassword = 'hashed_password';
        $referral = 'REF123';

        $this->passwordHasher
            ->expects($this->once())
            ->method('hashPassword')
            ->willReturn($hashedPassword);

        $this->referralReceiver
            ->expects($this->once())
            ->method('referral')
            ->willReturn($referral);

        $this->userRepository
            ->expects($this->once())
            ->method('save');

        // Act
        $user = $this->service->createUser($name, $email, $location, null, $password);

        // Assert
        $this->assertInstanceOf(UserInterface::class, $user);
        $this->assertEquals($name, $user->name());
        $this->assertEquals($email, $user->email());
        $this->assertNull($user->getPhone());
    }
}
