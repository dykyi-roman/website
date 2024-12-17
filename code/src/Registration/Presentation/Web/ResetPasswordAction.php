<?php

declare(strict_types=1);

namespace App\Registration\Presentation\Web;

use App\Registration\Presentation\Web\Request\ResetPasswordRequestDTO;
use App\Registration\Presentation\Web\Response\ForgotPasswordJsonResponder;
use App\Registration\Presentation\Web\Response\ResetPasswordJsonResponder;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class ResetPasswordAction
{
    #[Route('/reset-password', name: 'reset-password', methods: ['GET'])]
    public function __invoke(
        #[MapRequestPayload] ResetPasswordRequestDTO $request,
        ForgotPasswordJsonResponder $responder,
        TranslatorInterface $translator,
    ): ResetPasswordJsonResponder {
        try {
            return $responder->success($translator->trans('Password is changed!'))->respond();
        } catch (\Throwable $exception) {
            return $responder->error($exception)->respond();
        }
    }
}
