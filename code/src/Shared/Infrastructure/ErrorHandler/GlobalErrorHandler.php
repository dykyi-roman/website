<?php

declare(strict_types=1);

namespace Shared\Infrastructure\ErrorHandler;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final readonly class GlobalErrorHandler implements EventSubscriberInterface
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $request = $event->getRequest();
        if (!$this->supportsContentType($request->getAcceptableContentTypes())) {
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

    /** @param array<string> $contentTypes */
    protected function supportsContentType(array $contentTypes): bool
    {
        return in_array('application/json', $contentTypes, true);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }
}
