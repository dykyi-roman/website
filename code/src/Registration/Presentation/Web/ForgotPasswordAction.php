<?php

declare(strict_types=1);

namespace App\Registration\Presentation\Web;

use App\Registration\Presentation\Responder\ForgotPasswordJsonResponder;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final readonly class ForgotPasswordAction
{
    public function __construct(
        private ForgotPasswordJsonResponder $responder,
    ) {
    }

    #[Route('/forgot-password', name: 'forgot-password', methods: ['POST'])]
    public function login(): Response
    {
        try {
            //

            return $this->responder->success()->respond();
        } catch (\Throwable $exception) {
            return $this->responder->error($exception)->respond();
        }
    }
}
