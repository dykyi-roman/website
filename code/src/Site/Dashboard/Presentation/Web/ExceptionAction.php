<?php

declare(strict_types=1);

namespace Site\Dashboard\Presentation\Web;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class ExceptionAction extends AbstractController
{
    public function __invoke(\Throwable $exception): Response
    {
        $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
        $template = '@Dashboard/errors/500.html.twig';

        if ($exception instanceof NotFoundHttpException) {
            $statusCode = Response::HTTP_NOT_FOUND;
            $template = '@Dashboard/errors/404.html.twig';
        } elseif ($exception instanceof AccessDeniedHttpException) {
            $statusCode = Response::HTTP_FORBIDDEN;
            $template = '@Dashboard/errors/403.html.twig';
        }

        return $this->render($template, [
            'exception' => $exception,
        ], new Response('', $statusCode));
    }
}
