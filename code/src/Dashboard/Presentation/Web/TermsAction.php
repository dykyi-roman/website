<?php

declare(strict_types=1);

namespace App\Dashboard\Presentation\Web;

use App\Dashboard\Presentation\Responder\TermsHtmlResponder;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class TermsAction
{
    #[Route('/terms', name: 'terms')]
    public function terms(
        TermsHtmlResponder $responder,
        TranslatorInterface $translator,
    ): Response
    {
        return $responder
            ->withTermsData([
                'page_title' => $translator->trans('terms.page_title'),
                'page_description' => $translator->trans('terms.page_description'),
                'page_keywords' => $translator->trans('terms.page_keywords'),
                'content' => $translator->trans('terms.content'),
            ])
            ->respond();
    }
}
