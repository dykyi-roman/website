<?php

declare(strict_types=1);

namespace Profile\UserStatus\DomainModel\EventSubscriber;

use Profile\User\Application\UserAuthentication\Service\UserFetcherInterface;
use Profile\User\DomainModel\Exception\AuthenticationException;
use Profile\UserStatus\DomainModel\Dto\UserUpdateStatus;
use Profile\UserStatus\DomainModel\Service\UserStatusCacheInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;

final readonly class UserActivitySubscriber implements EventSubscriberInterface
{
    public function __construct(
        private UserFetcherInterface $userFetcher,
        private UserStatusCacheInterface $userStatusCache,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [['onKernelRequest', 20]],
        ];
    }

    public function onKernelRequest(): void
    {
        try {
            $user = $this->userFetcher->fetch();
        } catch (AuthenticationException) {
            return;
        }

        $this->userStatusCache->changeStatus(UserUpdateStatus::isOnline($user->id()));
    }
}