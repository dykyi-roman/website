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
        $searchResults = [
            [
                'title' => 'Sample Service Title 1',
                'description' => 'This is a sample description for the first service offering. This is a sample description for the first service offering. This is a sample description for the first service offering. This is a sample description for the first service offering.',
                'feedback_count' => '42',
                'image_url' => 'https://dykyi-roman.github.io/images/photo.jpg',
                'features' => [
                    'Super-premium',
                    'Master-freelancer',
                    '95% positive reviews',
                    'Online 4 hours ago',
                    'Response time: 2 hours'
                ],
                'rating' => 3,
                'review_count' => 23,
                'category' => 'Курсы',
                'price' => '500'
            ]];

        $countries = [
            ['code' => 'ua', 'name' => 'Ukraine'],
            ['code' => 'es', 'name' => 'Spain']
        ];

        return $this->render('@Dashboard/dashboard.html.twig', [
            'page_title' => 'Dashboard',
            'current_language' => 'UA',
            'search_results' => $searchResults,
            'countries' => $countries
        ]);
    }
}
