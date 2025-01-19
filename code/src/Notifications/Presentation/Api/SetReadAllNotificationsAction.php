<?php

declare(strict_types=1);

namespace Notifications\Presentation\Api;

use Notifications\DomainModel\Service\NotificationServiceInterface;
use Notifications\Presentation\Api\Response\DeleteNotificationJsonResponder;
use OpenApi\Attributes as OA;
use Profile\User\Application\UserAuthentication\Service\UserFetcherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class SetReadAllNotificationsAction
{
    #[Route('/v1/notifications', name: 'read_all_notifications', methods: ['PUT'])]
    #[OA\Put(
        path: '/api/v1/notifications',
        description: 'Marks all notifications as read for the authenticated user',
        summary: 'Mark all notifications as read',
        tags: ['Notifications']
    )]
    #[OA\Response(
        response: 200,
        description: 'All notifications marked as read successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'string', example: 'success'),
                new OA\Property(property: 'message', type: 'string', example: 'Ok'),
            ]
        )
    )]
    public function __invoke(
        UserFetcherInterface $userFetcher,
        NotificationServiceInterface $notificationService,
        DeleteNotificationJsonResponder $responder,
    ): DeleteNotificationJsonResponder {
        $notificationService->markAllAsRead($userFetcher->fetch()->id());

        return $responder->success('Ok')->respond();
    }
}
