<?php

declare(strict_types=1);

namespace App\Dashboard\Presentation\Web;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ContactAction extends AbstractController
{
    #[Route('/contact', name: 'contact')]
    public function __invoke(): Response
    {
        return $this->render('@Dashboard/contact.html.twig', [
            'page_title' => 'Contact Us',
            'content' => 'Some text',
        ]);
    }
}
