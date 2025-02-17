<?php

declare(strict_types=1);

namespace Site\Dashboard\Presentation\Web;

use Site\Dashboard\Presentation\Web\Response\CareersHtmlResponder;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class CareersAction
{
    #[Route('/careers', name: 'careers', methods: ['GET'])]
    public function terms(
        CareersHtmlResponder $responder,
        TranslatorInterface $translator,
    ): CareersHtmlResponder {
        return $responder->context([
            'page_title' => $translator->trans('careers.page_title'),
            'content' => [],
        ])->respond();
    }
}
