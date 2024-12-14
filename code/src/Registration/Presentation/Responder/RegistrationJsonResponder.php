<?php

declare(strict_types=1);

namespace App\Registration\Presentation\Responder;

use App\Shared\Presentation\Responder\ResponderInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class RegistrationJsonResponder implements ResponderInterface
{
    private array $data = [];
    private int $statusCode = Response::HTTP_OK;
    private array $headers = [];

    public function success(string $message = 'Registration successful'): self
    {
        $this->data = [
            'success' => true,
            'message' => $message,
        ];
        $this->statusCode = Response::HTTP_CREATED;

        return $this;
    }

    public function validationError(string $message, string $field = ''): self
    {
        $this->data = [
            'success' => false,
            'errors' => [
                'message' => $message,
                'field' => $field,
            ],
        ];
        $this->statusCode = Response::HTTP_BAD_REQUEST;

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
