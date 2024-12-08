<?php

declare(strict_types=1);

namespace App\Dashboard\Presentation\Web;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardAction extends AbstractController
{
    #[Route('/', name: 'dashboard_page')]
    public function __invoke(Request $request): Response
    {
        dump(
            $request->getLocale(),
            $request->getLanguages(),
            $request->getDefaultLocale(),
        ); die();

        return $this->render('@Dashboard/dashboard.html.twig', [
            'page_title' => 'Dashboard',
            'current_language' => 'UA',
            'search_results' => [],
        ]);
    }
}
