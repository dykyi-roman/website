<?php

declare(strict_types=1);

namespace Profile\User\Tests\Unit\Application\UserRegistration\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Profile\User\Application\UserRegistration\Service\SocialRegistrationService;
use Profile\User\DomainModel\Model\User;
use Profile\User\DomainModel\Model\UserInterface;
use Profile\User\DomainModel\Repository\UserRepositoryInterface;
use Shared\DomainModel\ValueObject\Country;
use Shared\DomainModel\ValueObject\Email;
use Shared\DomainModel\ValueObject\Location;
use Shared\DomainModel\ValueObject\UserId;
use Site\Registration\DomainModel\Service\ReferralReceiverInterface;

#[CoversClass(SocialRegistrationService::class)]
final class SocialRegistrationServiceTest extends TestCase
{
    private MockObject&UserRepositoryInterface $userRepository;
    private MockObject&ReferralReceiverInterface $referralReceiver;
    private SocialRegistrationService $service;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->referralReceiver = $this->createMock(ReferralReceiverInterface::class);
        $this->service = new SocialRegistrationService($this->userRepository, $this->referralReceiver);
    }

    public function testHasRegistrationByFacebookWhenUserExistsByFacebookId(): void
    {
        $email = Email::fromString('test@example.com');
        $facebookId = 'facebook123';
        $existingUser = $this->createMock(UserInterface::class);

        $this->userRepository
            ->expects(self::once())
            ->method('findByToken')
            ->with('facebookToken', $facebookId)
            ->willReturn($existingUser);

        $result = $this->service->hasRegistrationByFacebook($email, $facebookId);

        self::assertSame($existingUser, $result);
    }

    public function testHasRegistrationByFacebookWhenUserExistsByEmail(): void
    {
        $email = Email::fromString('test@example.com');
        $facebookId = 'facebook123';
        $existingUser = $this->createMock(UserInterface::class);

        $this->userRepository
            ->expects(self::once())
            ->method('findByToken')
            ->with('facebookToken', $facebookId)
            ->willReturn(null);

        $this->userRepository
            ->expects(self::once())
            ->method('findByEmail')
            ->with((string) $email)
            ->willReturn($existingUser);

        $existingUser
            ->expects(self::once())
            ->method('setFacebookToken')
            ->with($facebookId);

        $this->userRepository
            ->expects(self::once())
            ->method('save')
            ->with($existingUser);

        $result = $this->service->hasRegistrationByFacebook($email, $facebookId);

        self::assertSame($existingUser, $result);
    }

    public function testHasRegistrationByFacebookWhenUserDoesNotExist(): void
    {
        $email = Email::fromString('test@example.com');
        $facebookId = 'facebook123';

        $this->userRepository
            ->expects(self::once())
            ->method('findByToken')
            ->with('facebookToken', $facebookId)
            ->willReturn(null);

        $this->userRepository
            ->expects(self::once())
            ->method('findByEmail')
            ->with((string) $email)
            ->willReturn(null);

        $result = $this->service->hasRegistrationByFacebook($email, $facebookId);

        self::assertNull($result);
    }

    public function testHasRegistrationByGoogleWhenUserExistsByGoogleId(): void
    {
        $email = Email::fromString('test@example.com');
        $googleId = 'google123';
        $existingUser = $this->createMock(UserInterface::class);

        $this->userRepository
            ->expects(self::once())
            ->method('findByToken')
            ->with('googleToken', $googleId)
            ->willReturn($existingUser);

        $result = $this->service->hasRegistrationByGoogle($email, $googleId);

        self::assertSame($existingUser, $result);
    }

    public function testHasRegistrationByGoogleWhenUserExistsByEmail(): void
    {
        $email = Email::fromString('test@example.com');
        $googleId = 'google123';
        $existingUser = $this->createMock(UserInterface::class);

        $this->userRepository
            ->expects(self::once())
            ->method('findByToken')
            ->with('googleToken', $googleId)
            ->willReturn(null);

        $this->userRepository
            ->expects(self::once())
            ->method('findByEmail')
            ->with((string) $email)
            ->willReturn($existingUser);

        $existingUser
            ->expects(self::once())
            ->method('setGoogleToken')
            ->with($googleId);

        $this->userRepository
            ->expects(self::once())
            ->method('save')
            ->with($existingUser);

        $result = $this->service->hasRegistrationByGoogle($email, $googleId);

        self::assertSame($existingUser, $result);
    }

    public function testHasRegistrationByGoogleWhenUserDoesNotExist(): void
    {
        $email = Email::fromString('test@example.com');
        $googleId = 'google123';

        $this->userRepository
            ->expects(self::once())
            ->method('findByToken')
            ->with('googleToken', $googleId)
            ->willReturn(null);

        $this->userRepository
            ->expects(self::once())
            ->method('findByEmail')
            ->with((string) $email)
            ->willReturn(null);

        $result = $this->service->hasRegistrationByGoogle($email, $googleId);

        self::assertNull($result);
    }

    public function testCreateFacebookUser(): void
    {
        $userId = new UserId();
        $name = 'John Doe';
        $email = Email::fromString('john@example.com');
        $location = new Location(new Country('US'));
        $token = 'facebook123';
        $referral = 'ref123';

        $this->referralReceiver
            ->expects(self::once())
            ->method('referral')
            ->willReturn($referral);

        $this->userRepository
            ->expects(self::once())
            ->method('save')
            ->willReturnCallback(function (User $user) use ($userId, $name, $email, $location, $token, $referral) {
                self::assertSame($userId, $user->id());
                self::assertSame($name, $user->name());
                self::assertSame($email, $user->email());
                self::assertSame($location, $user->getLocation());
                self::assertSame($token, $user->getFacebookToken());
                self::assertSame($referral, $user->getReferral());

                return $user;
            });

        $result = $this->service->createFacebookUser($userId, $name, $email, $location, $token);

        self::assertInstanceOf(UserInterface::class, $result);
    }

    public function testCreateGoogleUser(): void
    {
        $userId = new UserId();
        $name = 'John Doe';
        $email = Email::fromString('john@example.com');
        $location = new Location(new Country('US'));
        $token = 'google123';
        $referral = 'ref123';

        $this->referralReceiver
            ->expects(self::once())
            ->method('referral')
            ->willReturn($referral);

        $this->userRepository
            ->expects(self::once())
            ->method('save')
            ->willReturnCallback(function (User $user) use ($userId, $name, $email, $location, $token, $referral) {
                self::assertSame($userId, $user->id());
                self::assertSame($name, $user->name());
                self::assertSame($email, $user->email());
                self::assertSame($location, $user->getLocation());
                self::assertSame($token, $user->getGoogleToken());
                self::assertSame($referral, $user->getReferral());

                return $user;
            });

        $result = $this->service->createGoogleUser($userId, $name, $email, $location, $token);

        self::assertInstanceOf(UserInterface::class, $result);
    }
}
