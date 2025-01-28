<?php

declare(strict_types=1);

namespace Profile\UserStatus\DomainModel\EventSubscriber;

use Profile\UserStatus\DomainModel\Dto\UserUpdateStatus;
use Profile\UserStatus\DomainModel\Event\UserWentOnlineEvent;
use Profile\UserStatus\DomainModel\Service\UserStatusInterface;
use Shared\DomainModel\Exception\AuthenticationException;
use Shared\DomainModel\Services\MessageBusInterface;
use Shared\DomainModel\Services\UserFetcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;

final readonly class UserActivitySubscriber implements EventSubscriberInterface
{
    public function __construct(
        private UserFetcherInterface $userFetcher,
        private UserStatusInterface $userStatus,
        private MessageBusInterface $eventBus,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [['onKernelRequest', -8]],
        ];
    }

    public function onKernelRequest(): void
    {
        try {
            $user = $this->userFetcher->fetch();
        } catch (AuthenticationException) {
            return;
        }

        $this->userStatus->changeStatus(UserUpdateStatus::createOnline($user->id()));

        $this->eventBus->dispatch(new UserWentOnlineEvent($user->id()));
    }
}
