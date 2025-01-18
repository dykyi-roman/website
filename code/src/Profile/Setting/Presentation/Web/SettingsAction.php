<?php

declare(strict_types=1);

namespace Profile\Setting\Presentation\Web;

use Profile\Setting\Presentation\Web\Response\SettingsHtmlResponder;
use Profile\User\Application\UserAuthentication\Service\UserFetcherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class SettingsAction
{
    #[Route('/settings', name: 'settings', methods: ['GET'])]
    public function __invoke(
        SettingsHtmlResponder $responder,
        TranslatorInterface $translator,
        UserFetcherInterface $userFetcher,
    ): SettingsHtmlResponder {
        $user = $userFetcher->fetch();

        return $responder->context([
            'page_title' => $translator->trans('settings.page_title'),
            'content' => '',
            'settings' => [
                'privacy' => [
                    'user_status' => $user->status()->isActive(),
                ],
            ],
        ])->respond();
    }
}
