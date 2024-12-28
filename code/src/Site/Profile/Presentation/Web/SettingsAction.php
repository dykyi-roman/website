<?php

declare(strict_types=1);

namespace Site\Profile\Presentation\Web;

use Site\Profile\Presentation\Web\Response\SettingsHtmlResponder;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class SettingsAction
{
    #[Route('/settings', name: 'settings')]
    public function __invoke(
        SettingsHtmlResponder $responder,
        TranslatorInterface $translator,
    ): SettingsHtmlResponder {
        return $responder->context([
            'page_title' => $translator->trans('settings.page_title'),
            'content' => '',
            'settings' => [
                'privacy' => [
                    'status' => 1 // 0
                ],
            ],
        ])->respond();
    }
}
