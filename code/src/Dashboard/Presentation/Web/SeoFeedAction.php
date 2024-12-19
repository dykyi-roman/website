<?php

declare(strict_types=1);

namespace App\Dashboard\Presentation\Web;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class SeoFeedAction extends AbstractController
{
    #[Route('/feed/rss.xml', name: 'feed-rss', defaults: ['_format' => 'xml'])]
    public function rss(): Response
    {
        // We get the latest articles/posts
        $items = [];

//        organization:
//           name: 'Название вашего сайта'
//           description: 'Описание вашего сайта'
//        website_url: 'https://your-domain.com'

        $response = new Response(
            $this->renderView('@Dashboard/feed/rss.xml.twig', [
                'items' => $items,
                'organization' => $this->getParameter('organization'),
                'website_url' => $this->getParameter('website_url'),
            ])
        );

        $response->headers->set('Content-Type', 'application/rss+xml; charset=UTF-8');

        return $response;
    }

    #[Route('/feed/atom.xml', name: 'feed-atom', defaults: ['_format' => 'xml'])]
    public function atom(): Response
    {
        // We get the latest articles/posts
        $items = [];

        $response = new Response(
            $this->renderView('@Dashboard/feed/atom.xml.twig', [
                'items' => $items,
                'organization' => $this->getParameter('organization'),
                'website_url' => $this->getParameter('website_url'),
            ])
        );

        $response->headers->set('Content-Type', 'application/atom+xml; charset=UTF-8');

        return $response;
    }
}
