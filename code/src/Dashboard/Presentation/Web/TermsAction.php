<?php

declare(strict_types=1);

namespace App\Dashboard\Presentation\Web;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TermsAction extends AbstractController
{
    #[Route('/terms', name: 'terms')]
    public function __invoke(): Response
    {
        return $this->render('@Dashboard/terms.html.twig', [
            'page_title' => 'Terms',
            'content' => 'Some terms text',
        ]);
    }
}
