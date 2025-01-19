<?php

declare(strict_types=1);

namespace Notifications\Presentation\Api;

use Notifications\DomainModel\Enum\UserNotificationId;
use Notifications\DomainModel\Service\NotificationServiceInterface;
use Notifications\Presentation\Api\Response\NotificationReadJsonResponder;
use Profile\User\Application\UserAuthentication\Service\UserFetcherInterface;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;

final class NotificationReadAction
{
    #[Route('/v1/notifications/{id}', methods: ['PUT'])]
    #[OA\Put(
        path: '/v1/notifications/{id}',
        description: 'Marks a specific notification as read for the authenticated user',
        summary: 'Mark notification as read',
        tags: ['Notifications']
    )]
    #[OA\Parameter(
        name: 'id',
        description: 'Notification ID',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Response(
        response: 200,
        description: 'Notification marked as read successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'string', example: 'success'),
                new OA\Property(property: 'message', type: 'string', example: 'Ok')
            ]
        )
    )]
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
