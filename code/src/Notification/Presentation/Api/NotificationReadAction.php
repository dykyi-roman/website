<?php

declare(strict_types=1);

namespace Notification\Presentation\Api;

use Notification\DomainModel\Enum\UserNotificationId;
use Notification\DomainModel\Service\NotificationServiceInterface;
use Notification\Presentation\Api\Response\NotificationReadJsonResponder;
use Profile\User\Application\GetCurrentUser\Service\UserFetcherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class NotificationReadAction
{
    #[Route('/v1/notifications/mark-read/{id}', methods: ['PATCH'])]
    public function __invoke(
        string $id,
        UserFetcherInterface $userFetcher,
        NotificationServiceInterface $notificationService,
        NotificationReadJsonResponder $responder,
    ): NotificationReadJsonResponder {
        $notificationService->markAsRead($userFetcher->fetch()->id(), UserNotificationId::fromString($id));

        return $responder->success('Ok')->respond();
    }
}
