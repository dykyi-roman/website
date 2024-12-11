<?php

declare(strict_types=1);

namespace App\Dashboard\Presentation\Web;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

final readonly class DashboardAction
{
    #[Route('/', name: 'dashboard')]
    public function __invoke(
        Environment $twig,
        TranslatorInterface $translator,
    ): Response {
        return new Response(
            $twig->render('@Dashboard/page/dashboard.html.twig', [
                'page_title' => $translator->trans('dashboard.page_title'),
            ])
        );
    }
}
