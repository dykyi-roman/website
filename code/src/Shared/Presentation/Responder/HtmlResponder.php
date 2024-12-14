<?php

declare(strict_types=1);

namespace App\Shared\Presentation\Responder;

use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

final class HtmlResponder extends AbstractResponder
{
    public function __construct(
        private readonly Environment $twig,
    ) {
    }

    protected function supportsContentType(array $contentTypes): bool
    {
        return in_array('text/html', $contentTypes, true);
    }

    protected function createResponse(ResponderInterface $result): Response
    {
        $content = $this->twig->render($result->template(), $result->payload());
        
        $response = new Response($content, $result->statusCode());
        $response->headers->set('Content-Type', 'text/html');
        
        return $response;
    }
}
