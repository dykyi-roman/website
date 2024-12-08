<?php

declare(strict_types=1);

namespace App\Dashboard\Presentation\Web;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class DashboardAction extends AbstractController
{
    #[Route('/', name: 'dashboard_page')]
    public function __invoke(
        Request $request,
        TranslatorInterface $translator,
    ): Response {
        return $this->render('@Dashboard/dashboard.html.twig', [
            'page_title' => $translator->trans('dashboard.page_title'),
        ]);
    }
}
