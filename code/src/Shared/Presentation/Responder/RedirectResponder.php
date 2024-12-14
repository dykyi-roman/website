<?php

declare(strict_types=1);

namespace App\Shared\Presentation\Responder;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class RedirectResponder implements ResponderInterface
{
    private string $url;
    private int $statusCode;
    private array $headers;

    public function __construct(string $url = '/', int $statusCode = Response::HTTP_FOUND, array $headers = [])
    {
        $this->url = $url;
        $this->statusCode = $statusCode;
        $this->headers = $headers;
    }

    public function withUrl(string $url): self
    {
        $this->url = $url;
        return $this;
    }

    public function withStatusCode(int $statusCode): self
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    public function withHeaders(array $headers): self
    {
        $this->headers = $headers;
        return $this;
    }

    public function respond(): Response
    {
        return new RedirectResponse($this->url, $this->statusCode, $this->headers);
    }
}
