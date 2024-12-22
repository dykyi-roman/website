<?php

declare(strict_types=1);

namespace Shared\Presentation\Responder;

use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

final class HtmlResponder extends AbstractResponder
{
    public function __construct(
        private readonly Environment $twig,
    ) {
    }

    /** @param array<string> $contentTypes */
    protected function supportsContentType(array $contentTypes): bool
    {
        return in_array('text/html', $contentTypes, true);
    }

    /**
     * @throws \Twig\Error\SyntaxError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\LoaderError
     */
    protected function createResponse(ResponderInterface $result): Response
    {
        if ($result instanceof TemplateResponderInterface) {
            $content = $this->twig->render($result->template(), $result->payload());
            $response = new Response($content, $result->statusCode());
            foreach ($result->headers() as $key => $value) {
                $response->headers->set($key, $value);
            }

            return $response;
        }

        return new Response();
    }
}
