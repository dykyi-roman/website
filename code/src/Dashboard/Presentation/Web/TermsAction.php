<?php

declare(strict_types=1);

namespace App\Dashboard\Presentation\Web;

use App\Dashboard\Presentation\Web\Responder\TermsHtmlResponder;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class TermsAction
{
    #[Route('/terms', name: 'terms')]
    public function terms(
        TermsHtmlResponder $responder,
        TranslatorInterface $translator,
    ): TermsHtmlResponder {
        return $responder->respond([
            'page_title' => $translator->trans('terms.page_title'),
            'content' => $translator->trans('terms.content', [
                '%last_updated_date%' => date('Y-m-d'),
            ]),
        ]);
    }
}
