<?php

declare(strict_types=1);

namespace Notifications\Presentation\Api;

use Notifications\DomainModel\Service\NotificationServiceInterface;
use Notifications\Presentation\Api\Response\DeleteNotificationJsonResponder;
use OpenApi\Attributes as OA;
use Profile\User\Application\UserAuthentication\Service\UserFetcherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class DeleteAllNotificationsAction
{
    #[Route('/v1/notifications', name: 'delete_all_notifications', methods: ['DELETE'])]
    #[OA\Delete(
        path: '/api/v1/notifications',
        description: 'Marks all notifications as deleted for the authenticated user',
        summary: 'Mark all notifications as deleted',
        tags: ['Notifications']
    )]
    #[OA\Response(
        response: 200,
        description: 'All notifications marked as deleted successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'string', example: 'success'),
                new OA\Property(property: 'message', type: 'string', example: 'All notifications deleted'),
            ]
        )
    )]
    public function __invoke(
        UserFetcherInterface $userFetcher,
        NotificationServiceInterface $notificationService,
        DeleteNotificationJsonResponder $responder,
    ): DeleteNotificationJsonResponder {
        $notificationService->markAllAsDeleted($userFetcher->fetch()->id());

        return $responder->success('Ok')->respond();
    }
}
