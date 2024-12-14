<?php

declare(strict_types=1);

namespace App\Shared\Presentation\Responder;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;

final readonly class HtmlResponder implements EventSubscriberInterface
{
    public function __construct(
        protected Environment $twig,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => ['onKernelView'],
        ];
    }

    public function onKernelView(ViewEvent $viewEvent): void
    {
        $request = $viewEvent->getRequest();

        if (!in_array('text/html', $request->getAcceptableContentTypes(), true)) {
            return;
        }

        $result = $viewEvent->getControllerResult();
        $content = $this->twig->render($result->template(), $result->payload());

        $response = new Response($content);
        $response->headers->set('Content-Type', 'text/html');

        $viewEvent->setResponse($response);
    }
}
