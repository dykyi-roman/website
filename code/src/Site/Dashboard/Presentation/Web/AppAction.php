<?php

declare(strict_types=1);

namespace Site\Dashboard\Presentation\Web;

use Site\Dashboard\Application\Service\VoteService;
use Site\Dashboard\Presentation\Web\Response\AppHtmlResponder;
use Site\Dashboard\Presentation\Web\Response\AppJsonResponder;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class AppAction
{
    public function __construct(
        private VoteService $voteService,
    ) {
    }

    #[Route('/votes/app/{app}', name: 'app-page', methods: ['GET'])]
    public function show(
        AppHtmlResponder $responder,
        TranslatorInterface $translator,
        string $app,
    ): AppHtmlResponder {
        return $responder->context([
            'page_title' => $translator->trans('app.page_title'),
            'content' => $translator->trans('app.page_context'),
            'app_current_votes' => $this->voteService->getVotes($app)->getCount(),
            'app_total_votes' => 10000,
            'type' => $app,
        ])->respond();
    }

    #[Route('/votes/app/{app}', name: 'app-action', methods: ['POST'])]
    public function action(
        AppJsonResponder $responder,
        string $app,
    ): AppJsonResponder {
        $this->voteService->incrementVotes($app);

        return $responder->success('Ok')->respond();
    }
}
