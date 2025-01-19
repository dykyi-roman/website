<?php

declare(strict_types=1);

namespace Notifications\Presentation\Web;

use Notifications\DomainModel\Service\NotificationServiceInterface;
use Notifications\Presentation\Web\Request\NotificationsRequestDto;
use Notifications\Presentation\Web\Response\NotificationsHtmlResponder;
use Profile\User\Application\UserAuthentication\Service\UserFetcherInterface;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class NotificationsAction
{
    #[Route('/notifications', name: 'notifications', methods: ['GET'])]
    public function __invoke(
//        #[MapQueryString] NotificationsRequestDto $request,
        NotificationServiceInterface $notificationService,
        UserFetcherInterface $userFetcher,
        TranslatorInterface $translator,
        NotificationsHtmlResponder $responder,
    ): NotificationsHtmlResponder {
        return $responder->context([
            'page_title' => $translator->trans('notifications.page_title'),
            'content' => '',
            'notifications' => $notificationService->getUserNotifications(
                $userFetcher->fetch()->id(),
//                $request->page,
//                $request->limit,
            )->items,
        ])->respond();
    }
}
