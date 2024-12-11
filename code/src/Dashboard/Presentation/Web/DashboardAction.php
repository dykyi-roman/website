<?php

declare(strict_types=1);

namespace App\Dashboard\Presentation\Web;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

final readonly class DashboardAction
{
    public function __construct(
        private Environment $twig,
        private TranslatorInterface $translator,
    ) {
    }

    #[Route('/', name: 'dashboard')]
    public function __invoke(Request $request): Response
    {
        return new Response(
            $this->twig->render('@Dashboard/dashboard.html.twig', [
                'page_title' => $this->translator->trans('dashboard.page_title'),
            ])
        );
    }
}
