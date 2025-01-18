<?php

declare(strict_types=1);

namespace Profile\User\Tests\Unit\Application\UserAuthentication\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Profile\User\Application\UserAuthentication\Service\UserFetcher;
use Profile\User\DomainModel\Exception\AuthenticationException;
use Profile\User\DomainModel\Model\UserInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

#[CoversClass(UserFetcher::class)]
final class UserFetcherTest extends TestCase
{
    /** @var MockObject&Security */
    private MockObject $security;
    private UserFetcher $userFetcher;

    protected function setUp(): void
    {
        $this->security = $this->createMock(Security::class);
        $this->userFetcher = new UserFetcher($this->security);
    }

    public function testIsLoginReturnsTrueWhenTokenExists(): void
    {
        /** @var MockObject&TokenInterface */
        $token = $this->createMock(TokenInterface::class);

        $this->security
            ->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        self::assertTrue($this->userFetcher->isLogin());
    }

    public function testIsLoginReturnsFalseWhenTokenDoesNotExist(): void
    {
        $this->security
            ->expects($this->once())
            ->method('getToken')
            ->willReturn(null);

        self::assertFalse($this->userFetcher->isLogin());
    }

    public function testFetchReturnsUserWhenTokenAndUserExist(): void
    {
        /** @var MockObject&UserInterface */
        $user = $this->createMock(UserInterface::class);
        /** @var MockObject&TokenInterface */
        $token = $this->createMock(TokenInterface::class);

        $token
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $this->security
            ->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $result = $this->userFetcher->fetch();

        self::assertSame($user, $result);
    }

    public function testFetchThrowsExceptionWhenTokenDoesNotExist(): void
    {
        $this->security
            ->expects($this->once())
            ->method('getToken')
            ->willReturn(null);

        $this->expectException(AuthenticationException::class);

        $this->userFetcher->fetch();
    }

    public function testFetchThrowsExceptionWhenUserIsNotUserInterface(): void
    {
        /** @var MockObject&TokenInterface */
        $token = $this->createMock(TokenInterface::class);

        $token
            ->expects($this->once())
            ->method('getUser')
            ->willReturn(null);

        $this->security
            ->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $this->expectException(AuthenticationException::class);

        $this->userFetcher->fetch();
    }
}
