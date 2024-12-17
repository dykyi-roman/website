<?php

declare(strict_types=1);

namespace App\Registration\Presentation\Web;

use App\Registration\Presentation\Web\Request\ForgotPasswordRequestDTO;
use App\Registration\Presentation\Web\Response\ForgotPasswordJsonResponder;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Notifier\Recipient\Recipient;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class ForgotPasswordAction
{
    #[Route('/forgot-password', name: 'forgot-password', methods: ['POST'])]
    public function __invoke(
        #[MapRequestPayload] ForgotPasswordRequestDTO $request,
        ForgotPasswordJsonResponder $responder,
        NotifierInterface $notifier,
        TranslatorInterface $translator,
    ): ForgotPasswordJsonResponder {
        try {
            $notification = (new Notification('Password Reset Request'))
                ->content('Click the link below to reset your password.');

            $recipient = new Recipient(
                (string) $request->email()
            );
            
            $notifier->send($notification, $recipient);

            return $responder->success($translator->trans('Letter sent. Check your email.'))->respond();
        } catch (\Throwable $exception) {
            return $responder->error($exception)->respond();
        }
    }
}
