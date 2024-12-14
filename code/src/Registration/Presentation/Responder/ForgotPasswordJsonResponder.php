<?php

declare(strict_types=1);

namespace App\Registration\Presentation\Responder;

use App\Shared\Presentation\Responder\ResponderInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ForgotPasswordJsonResponder implements ResponderInterface
{
    private array $data = [];
    private int $statusCode = Response::HTTP_OK;
    private array $headers = [];

    public function __construct(
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function success(): self
    {
        $this->data = [
            'success' => true,
            'message' => $this->translator->trans('Letter sent. Check your email.'),
        ];
        $this->statusCode = Response::HTTP_OK;

        return $this;
    }

    public function error(\Throwable $exception): self
    {
        $this->data = [
            'success' => false,
            'errors' => [
                'message' => $exception->getMessage(),
            ],
        ];
        $this->statusCode = Response::HTTP_BAD_REQUEST;

        return $this;
    }

    public function respond(): Response
    {
        return new JsonResponse($this->data, $this->statusCode, $this->headers);
    }
}
