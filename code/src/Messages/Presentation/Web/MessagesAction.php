<?php

declare(strict_types=1);

namespace Messages\Presentation\Web;

use Messages\Presentation\Web\Response\MessagesHtmlResponder;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class MessagesAction
{
    #[Route('/messages', name: 'messages', methods: ['GET'])]
    public function __invoke(
        MessagesHtmlResponder $responder,
        TranslatorInterface $translator,
    ): MessagesHtmlResponder {
        return $responder->context([
            'page_title' => $translator->trans('messages.page_title'),
            'content' => '',
        ])->respond();
    }
}
