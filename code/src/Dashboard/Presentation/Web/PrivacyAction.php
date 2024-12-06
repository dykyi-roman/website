<?php

declare(strict_types=1);

namespace App\Dashboard\Presentation\Web;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PrivacyAction extends AbstractController
{
    #[Route('/privacy', name: 'privacy')]
    public function __invoke(): Response
    {
        return $this->render('@Dashboard/privacy.html.twig', [
            'page_title' => 'Privacy Policy',
            'content' => 'Some privacy text',
        ]);
    }
}
