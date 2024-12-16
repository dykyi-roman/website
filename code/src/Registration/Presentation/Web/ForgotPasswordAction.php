<?php

declare(strict_types=1);

namespace App\Registration\Presentation\Web;

use App\Registration\Presentation\Web\Request\ForgotPasswordRequestDTO;
use App\Registration\Presentation\Web\Response\ForgotPasswordJsonResponder;
use App\Shared\DomainModel\Notification\EmailNotification;
use App\Shared\DomainModel\Services\NotificationInterface;
use App\Shared\DomainModel\ValueObject\Notification;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class ForgotPasswordAction
{
    #[Route('/forgot-password', name: 'forgot-password', methods: ['POST'])]
    public function login(
        #[MapRequestPayload] ForgotPasswordRequestDTO $request,
        TranslatorInterface $translator,
        ForgotPasswordJsonResponder $responder,
        NotificationInterface $notification,
    ): ForgotPasswordJsonResponder {
        try {
            $notification->send(
                new Notification(
                    subject: $translator->trans('Please reset your password'),
                    channels: ['email'],
                ),
                new EmailNotification($request->email()),
            );

            return $responder->success($translator->trans('Letter sent. Check your email.'))->respond();
        } catch (\Throwable $exception) {
            return $responder->error($exception)->respond();
        }
    }
}
