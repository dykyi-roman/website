<?php

declare(strict_types=1);

namespace Site\Profile\Presentation\Web;

use Site\Profile\Presentation\Web\Response\FavoritesHtmlResponder;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class FavoritesAction
{
    #[Route('/favorites', name: 'favorites')]
    public function __invoke(
        FavoritesHtmlResponder $responder,
        TranslatorInterface $translator,
    ): FavoritesHtmlResponder {

        return $responder->context([
            'page_title' => $translator->trans('favorites.page_title'),
            'content' => '',
        ])->respond();
    }
}
