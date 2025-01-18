<?php

declare(strict_types=1);

namespace Notifications\Presentation\Api;

use Notifications\DomainModel\Enum\UserNotificationId;
use Notifications\DomainModel\Service\NotificationServiceInterface;
use Notifications\Presentation\Api\Response\NotificationReadJsonResponder;
use Profile\User\Application\UserAuthentication\Service\UserFetcherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class NotificationReadAction
{
    #[Route('/v1/notifications/{id}', methods: ['PUT'])]
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
