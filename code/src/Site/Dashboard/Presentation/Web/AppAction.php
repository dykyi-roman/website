<?php

declare(strict_types=1);

namespace Site\Dashboard\Presentation\Web;

use Psr\SimpleCache\CacheInterface;
use Site\Dashboard\Presentation\Web\Response\AppHtmlResponder;
use Site\Dashboard\Presentation\Web\Response\AppJsonResponder;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class AppAction
{
    #[Route('/votes/app/{app}', name: 'app-page', methods: ['GET'])]
    public function show(
        AppHtmlResponder $responder,
        TranslatorInterface $translator,
        CacheInterface $cache,
        string $app,
    ): AppHtmlResponder {
        return $responder->context([
            'page_title' => $translator->trans('app.page_title'),
            'content' => $translator->trans('app.page_context'),
            'app_current_votes' => $cache->get("app_votes_{$app}") ?? 0,
            'app_total_votes' => 10000,
            'type' => $app,
        ])->respond();
    }

    #[Route('/votes/app/{app}', name: 'app-action', methods: ['POST'])]
    public function action(
        AppJsonResponder $responder,
        CacheInterface $cache,
        string $app,
    ): AppJsonResponder {
        $currentVotes = $cache->get("app_votes_{$app}") ?? 0;
        $newVotes = $currentVotes + 1;
        $cache->set("app_votes_{$app}", $newVotes);

        return $responder->success('Ok')->respond();
    }
}
