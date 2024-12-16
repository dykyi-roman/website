<?php

declare(strict_types=1);

namespace App\Registration\Presentation\Web;

use App\Registration\Presentation\Web\Request\ForgotPasswordRequestDTO;
use App\Registration\Presentation\Web\Response\ForgotPasswordJsonResponder;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\Recipient\Recipient;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class ForgotPasswordAction
{
    #[Route('/forgot-password', name: 'forgot-password', methods: ['POST'])]
    public function login(
        #[MapRequestPayload] ForgotPasswordRequestDTO $request,
        TranslatorInterface $translator,
        ForgotPasswordJsonResponder $responder,
        NotifierInterface $notification,
    ): ForgotPasswordJsonResponder {
        try {
            $notification->send(
                (new Notification($translator->trans('Please reset your password'))),
                new Recipient($request->email()->value)
            );

            return $responder->success($translator->trans('Letter sent. Check your email.'))->respond();
        } catch (\Throwable $exception) {
            return $responder->error($exception)->respond();
        }
    }
}
