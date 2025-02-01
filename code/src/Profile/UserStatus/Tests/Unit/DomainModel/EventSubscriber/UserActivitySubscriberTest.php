<?php

declare(strict_types=1);

namespace Profile\UserStatus\Tests\Unit\DomainModel\EventSubscriber;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Profile\UserStatus\DomainModel\Dto\UserUpdateStatus;
use Profile\UserStatus\DomainModel\Event\UserWentOnlineEvent;
use Profile\UserStatus\DomainModel\EventSubscriber\UserActivitySubscriber;
use Profile\UserStatus\DomainModel\Service\UserStatusInterface;
use Shared\DomainModel\Exception\AuthenticationException;
use Shared\DomainModel\Services\MessageBusInterface;
use Shared\DomainModel\Services\UserFetcherInterface;
use Shared\DomainModel\ValueObject\UserId;
use Symfony\Component\HttpKernel\KernelEvents;

#[CoversClass(UserActivitySubscriber::class)]
final class UserActivitySubscriberTest extends TestCase
{
    /** @var UserFetcherInterface&MockObject */
    private MockObject $userFetcher;

    /** @var UserStatusInterface&MockObject */
    private MockObject $userStatus;

    /** @var MessageBusInterface&MockObject */
    private MockObject $eventBus;
    private UserActivitySubscriber $subscriber;
    private string $validUuid;

    protected function setUp(): void
    {
        $this->validUuid = '550e8400-e29b-41d4-a716-446655440000';
        $this->userFetcher = $this->createMock(UserFetcherInterface::class);
        $this->userStatus = $this->createMock(UserStatusInterface::class);
        $this->eventBus = $this->createMock(MessageBusInterface::class);

        $this->subscriber = new UserActivitySubscriber(
            $this->userFetcher,
            $this->userStatus,
            $this->eventBus
        );
    }

    public function testGetSubscribedEvents(): void
    {
        $events = UserActivitySubscriber::getSubscribedEvents();

        $this->assertArrayHasKey(KernelEvents::REQUEST, $events);
        $this->assertEquals([['onKernelRequest', -8]], $events[KernelEvents::REQUEST]);
    }

    public function testOnKernelRequestWithAuthenticatedUser(): void
    {
        $userId = new UserId($this->validUuid);

        // Mock user object
        $user = $this->createMock(\Profile\User\DomainModel\Model\UserInterface::class);
        $user->method('id')->willReturn($userId);

        // Configure mocks
        $this->userFetcher
            ->expects($this->once())
            ->method('fetch')
            ->willReturn($user);

        $this->userStatus
            ->expects($this->once())
            ->method('changeStatus')
            ->with($this->callback(function (UserUpdateStatus $status) use ($userId) {
                return $status->userId === $userId
                    && true === $status->isOnline
                    && $status->lastOnlineAt instanceof \DateTimeImmutable;
            }));

        $this->eventBus
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(function (UserWentOnlineEvent $event) use ($userId) {
                return $event->userId === $userId;
            }));

        $this->subscriber->onKernelRequest();
    }

    public function testOnKernelRequestWithUnauthenticatedUser(): void
    {
        // Configure mock to throw authentication exception
        $this->userFetcher
            ->expects($this->once())
            ->method('fetch')
            ->willThrowException(new AuthenticationException());

        // Ensure other services are not called
        $this->userStatus
            ->expects($this->never())
            ->method('changeStatus');

        $this->eventBus
            ->expects($this->never())
            ->method('dispatch');

        // This should not throw an exception
        $this->subscriber->onKernelRequest();
    }
}
