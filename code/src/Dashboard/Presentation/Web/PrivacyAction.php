<?php

declare(strict_types=1);

namespace App\Dashboard\Presentation\Web;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PrivacyAction extends AbstractController
{
    #[Route('/privacy', name: 'privacy_page')]
    public function __invoke(): Response
    {
        $countries = [
            ['code' => 'ua', 'name' => 'Ukraine'],
            ['code' => 'es', 'name' => 'Spain']
        ];

        return $this->render('@Dashboard/privacy.html.twig', [
            'page_title' => 'Privacy Policy',
            'content' => 'Some privacy text',
            'countries' => $countries
        ]);
    }
}
