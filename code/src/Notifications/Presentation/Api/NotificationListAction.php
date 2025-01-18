<?php

declare(strict_types=1);

namespace Notifications\Presentation\Api;

use Notifications\DomainModel\Service\NotificationServiceInterface;
use Notifications\Presentation\Api\Request\NotificationListDto;
use Notifications\Presentation\Api\Response\NotificationListJsonResponder;
use Profile\User\Application\UserAuthentication\Service\UserFetcherInterface;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

class NotificationListAction
{
    #[Route('/v1/notifications', name: 'api_notifications_list', methods: ['GET'])]
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
