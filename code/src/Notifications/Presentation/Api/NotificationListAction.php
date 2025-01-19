<?php

declare(strict_types=1);

namespace Notifications\Presentation\Api;

use Notifications\DomainModel\Service\NotificationServiceInterface;
use Notifications\Presentation\Api\Request\NotificationListDto;
use Notifications\Presentation\Api\Response\NotificationListJsonResponder;
use OpenApi\Attributes as OA;
use Profile\User\Application\UserAuthentication\Service\UserFetcherInterface;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

class NotificationListAction
{
    #[Route('/v1/notifications', name: 'api_notifications_list', methods: ['GET'])]
    #[OA\Get(
        path: '/v1/notifications',
        description: 'Retrieves a list of notifications for the authenticated user',
        summary: 'Get user notifications',
        tags: ['Notifications']
    )]
    #[OA\Parameter(
        name: 'page',
        description: 'Page number',
        in: 'query',
        schema: new OA\Schema(type: 'integer', default: 1)
    )]
    #[OA\Parameter(
        name: 'limit',
        description: 'Number of items per page',
        in: 'query',
        schema: new OA\Schema(type: 'integer', default: 10)
    )]
    #[OA\Parameter(
        name: 'includeCount',
        description: 'Include unread notifications count',
        in: 'query',
        schema: new OA\Schema(type: 'boolean', default: false)
    )]
    #[OA\Response(
        response: 200,
        description: 'Notifications retrieved successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'string', example: 'success'),
                new OA\Property(property: 'message', type: 'string', example: 'Notifications retrieved successfully'),
                new OA\Property(property: 'data', type: 'object'),
            ]
        )
    )]
    public function __invoke(
        #[MapQueryString] NotificationListDto $request,
        NotificationServiceInterface $notificationService,
        UserFetcherInterface $userFetcher,
        NotificationListJsonResponder $responder,
    ): NotificationListJsonResponder {
        $userId = $userFetcher->fetch()->id();
        if ($request->includeCount) {
            $data['unread_count'] = $notificationService->getUnreadCount($userId);
        } else {
            $data = $notificationService->getUserNotifications(
                $userId,
                $request->page,
                $request->limit,
            );
        }

        return $responder->success($data, 'Notifications retrieved successfully')->respond();
    }
}
