<?php

declare(strict_types=1);

namespace App\Dashboard\Presentation\Web;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

final readonly class TermsAction
{
    public function __construct(
        private Environment $twig,
        private TranslatorInterface $translator,
    ) {
    }

    #[Route('/terms', name: 'terms')]
    public function __invoke(Request $request): Response
    {
        $termsContent = $this->translator->trans('terms.content');
        $termsContent = str_replace('%last_updated_date%', date('Y-m-d'), $termsContent);

        return new Response(
            $this->twig->render('@Dashboard/page/terms.html.twig', [
                'page_title' => $this->translator->trans('terms.page_title'),
                'content' => $termsContent,
            ])
        );
    }
}
