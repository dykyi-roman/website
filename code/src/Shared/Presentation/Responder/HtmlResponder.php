<?php

declare(strict_types=1);

namespace App\Shared\Presentation\Responder;

use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

abstract class HtmlResponder implements ResponderInterface
{
    protected Environment $twig;
    protected array $data = [];
    protected string $template;
    protected int $statusCode = Response::HTTP_OK;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    public function withTemplate(string $template): self
    {
        $this->template = $template;
        return $this;
    }

    public function withData(array $data): self
    {
        $this->data = $data;
        return $this;
    }

    public function withStatusCode(int $statusCode): self
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    public function respond(): Response
    {
        $content = $this->twig->render($this->template, $this->data);

        return new Response($content, $this->statusCode);
    }
}
