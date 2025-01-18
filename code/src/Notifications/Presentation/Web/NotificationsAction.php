<?php

declare(strict_types=1);

namespace Notifications\Presentation\Web;

use Notifications\Presentation\Web\Response\NotificationsHtmlResponder;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class NotificationsAction
{
    #[Route('/notifications', name: 'notifications', methods: ['GET'])]
    public function __invoke(
        NotificationsHtmlResponder $responder,
        TranslatorInterface $translator,
    ): NotificationsHtmlResponder {
        return $responder->context([
            'page_title' => $translator->trans('notifications.page_title'),
            'content' => '',
        ])->respond();
    }
}
