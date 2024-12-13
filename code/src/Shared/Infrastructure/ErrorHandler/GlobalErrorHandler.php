<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\ErrorHandler;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

final class GlobalErrorHandler
{
    public function __construct(
        private readonly array $validationRoutes,
    ) {
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $request = $event->getRequest();
        $routeName = $request->attributes->get('_route');
        if (!in_array($routeName, $this->validationRoutes, true)) {
            return;
        }

        $exception = $event->getThrowable();

        $errorResponse = [
            'success' => false,
            'errors' => [
                'message' => $exception->getMessage(),
            ],
        ];

        $response = new JsonResponse($errorResponse, Response::HTTP_UNPROCESSABLE_ENTITY);
        $event->setResponse($response);
    }
}
