<?php

declare(strict_types=1);

namespace App\Registration\Presentation\Web;

use App\Registration\Presentation\Web\Request\ForgotPasswordRequestDTO;
use App\Registration\Presentation\Web\Response\ForgotPasswordJsonResponder;
use App\Shared\EmailNotification;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
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
        EmailNotification $notification,
    ): ForgotPasswordJsonResponder {
        try {
            $resetNotification = (new Notification($translator->trans('Please reset your password')))
                ->content($translator->trans('Click the link below to reset your password:') . "\n" .
                    'https://example.com/reset-password?token=your-token-here');  // Replace with actual token generation

            $notification->send(
                $resetNotification,
                new Recipient($request->email()->value)
            );

            return $responder->success($translator->trans('Letter sent. Check your email.'))->respond();
        } catch (\Throwable $exception) {
            return $responder->error($exception)->respond();
        }
    }
}
