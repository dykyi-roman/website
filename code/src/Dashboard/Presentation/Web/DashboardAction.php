<?php

declare(strict_types=1);

namespace App\Dashboard\Presentation\Web;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardAction extends AbstractController
{
    #[Route('/', name: 'dashboard')]
    public function __invoke(): Response
    {
        $countries = [
            ['code' => 'ua', 'name' => 'Ukraine'],
            ['code' => 'es', 'name' => 'Spain']
        ];

        return $this->render('@Dashboard/dashboard.html.twig', [
            'page_title' => 'Dashboard',
            'current_language' => 'UA',
            'search_results' => [],
            'countries' => $countries
        ]);
    }
}
