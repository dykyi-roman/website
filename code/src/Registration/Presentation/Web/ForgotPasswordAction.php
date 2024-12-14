<?php

declare(strict_types=1);

namespace App\Registration\Presentation\Web;

use App\Registration\Presentation\Responder\ForgotPasswordJsonResponder;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class ForgotPasswordAction
{
    #[Route('/forgot-password', name: 'forgot-password', methods: ['POST'])]
    public function login(
        TranslatorInterface $translator,
        ForgotPasswordJsonResponder $responder,
    ): ForgotPasswordJsonResponder {
        try {
            //

            return $responder->success($translator->trans('Letter sent. Check your email.'))->respond();
        } catch (\Throwable $exception) {
            return $responder->error($exception)->respond();
        }
    }
}
