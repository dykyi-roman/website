<?php

declare(strict_types=1);

namespace App\Dashboard\Presentation\Web;

use App\Dashboard\Presentation\Responder\PrivacyHtmlResponder;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class PrivacyAction
{
    #[Route('/privacy', name: 'privacy')]
    public function privacy(
        PrivacyHtmlResponder $responder,
        TranslatorInterface $translator,
    ): Response {
        return $responder
            ->withPrivacyData([
                'page_title' => $translator->trans('privacy.page_title'),
                'page_description' => $translator->trans('privacy.page_description'),
                'page_keywords' => $translator->trans('privacy.page_keywords'),
                'content' => $translator->trans('privacy.content'),
            ])
            ->respond();
    }
}
