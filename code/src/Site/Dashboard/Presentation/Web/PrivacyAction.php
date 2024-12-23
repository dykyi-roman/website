<?php

declare(strict_types=1);

namespace Site\Dashboard\Presentation\Web;

use Site\Dashboard\Presentation\Web\Response\PrivacyHtmlResponder;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class PrivacyAction
{
    #[Route('/privacy', name: 'privacy')]
    public function privacy(
        PrivacyHtmlResponder $responder,
        TranslatorInterface $translator,
    ): PrivacyHtmlResponder {
        return $responder
            ->context([
                'page_title' => $translator->trans('privacy.page_title'),
                'content' => $translator->trans('privacy.content', [
                    '%last_updated_date%' => date('Y-m-d'),
                ]),
            ])
            ->respond();
    }
}
