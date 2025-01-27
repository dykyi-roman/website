<?php

declare(strict_types=1);

namespace Profile\UserStatus\DomainModel\EventSubscriber;

use Profile\User\DomainModel\Model\UserInterface;
use Profile\UserStatus\DomainModel\Dto\UserUpdateStatus;
use Profile\UserStatus\DomainModel\Event\UserWentOnlineEvent;
use Profile\UserStatus\DomainModel\Service\UserStatusInterface;
use Shared\DomainModel\Services\MessageBusInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;

final readonly class UserActivitySubscriber implements EventSubscriberInterface
{
    public function __construct(
        private Security $security,
        private UserStatusInterface $userStatus,
        private MessageBusInterface $eventBus,
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
        $token = $this->security->getToken();
        if (null === $token) {
            return;
        }

        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return;
        }

        $this->userStatus->changeStatus(UserUpdateStatus::createOnline($user->id()));

        $this->eventBus->dispatch(new UserWentOnlineEvent($user->id()));
    }
}
