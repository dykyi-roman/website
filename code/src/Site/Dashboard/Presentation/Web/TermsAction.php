<?php

declare(strict_types=1);

namespace Site\Dashboard\Presentation\Web;

use Site\Dashboard\Presentation\Web\Response\TermsHtmlResponder;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class TermsAction
{
    #[Route('/terms', name: 'terms')]
    public function terms(
        TermsHtmlResponder $responder,
        TranslatorInterface $translator,
    ): TermsHtmlResponder {
        return $responder
            ->context([
                'page_title' => $translator->trans('terms.page_title'),
                'content' => $translator->trans('terms.content', [
                    '%last_updated_date%' => date('Y-m-d'),
                ]),
            ])
            ->respond();
    }
}
