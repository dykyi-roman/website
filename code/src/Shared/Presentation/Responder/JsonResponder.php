<?php

namespace App\Shared\Presentation\Responder;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class JsonResponder implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => ['onKernelView'],
        ];
    }

    public function onKernelView(ViewEvent $viewEvent): void
    {
        $request = $viewEvent->getRequest();

        //        if (!in_array('application/json', $request->getAcceptableContentTypes(), true)) {
        //            return;
        //        }

        $result = $viewEvent->getControllerResult();
        if (!$result instanceof ResponderInterface) {
            return;
        }

        $viewEvent->setResponse(new JsonResponse($result->payload(), $result->statusCode()));
    }
}
