<?php

declare(strict_types=1);

namespace Notifications\Presentation\Api;

use Notifications\DomainModel\Service\NotificationServiceInterface;
use Notifications\DomainModel\ValueObject\UserNotificationId;
use Notifications\Presentation\Api\Response\ReadNotificationJsonResponder;
use OpenApi\Attributes as OA;
use Shared\DomainModel\Services\UserFetcherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class SetReadNotificationAction
{
    #[Route('/v1/notifications/{id}', name: 'read_notifications', methods: ['PUT'])]
    #[OA\Put(
        path: '/api/v1/notifications/{id}',
        operationId: 'markNotificationAsRead',
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
                new OA\Property(property: 'message', type: 'string', example: 'Ok'),
            ]
        )
    )]
    public function __invoke(
        string $id,
        UserFetcherInterface $userFetcher,
        NotificationServiceInterface $notificationService,
        ReadNotificationJsonResponder $responder,
    ): ReadNotificationJsonResponder {
        $notificationService->markAsRead($userFetcher->fetch()->id(), UserNotificationId::fromString($id));

        return $responder->success('Ok')->respond();
    }
}
