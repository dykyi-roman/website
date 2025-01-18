<?php

declare(strict_types=1);

namespace Notification\Presentation\Api;

use Notification\DomainModel\Service\NotificationServiceInterface;
use Notification\Presentation\Api\Response\NotificationCountJsonResponder;
use Profile\User\Application\GetCurrentUser\Service\UserFetcherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class NotificationCountAction
{
    #[Route('/v1/notifications/count', methods: ['GET'])]
    public function __invoke(
        UserFetcherInterface $userFetcher,
        NotificationServiceInterface $notificationService,
        NotificationCountJsonResponder $responder,
    ): NotificationCountJsonResponder {
        $notificationService->getUnreadCount($userFetcher->fetch()->id());

        return $responder->success('Ok')->respond();
    }
}
