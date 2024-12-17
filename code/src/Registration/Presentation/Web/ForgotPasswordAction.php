<?php

declare(strict_types=1);

namespace App\Registration\Presentation\Web;

use App\Registration\Presentation\Web\Request\ForgotPasswordRequestDTO;
use App\Registration\Presentation\Web\Response\ForgotPasswordJsonResponder;
use App\Shared\DomainModel\Services\NotificationInterface;
use App\Shared\DomainModel\ValueObject\Notification;
use App\Shared\Infrastructure\Notification\Recipient;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class ForgotPasswordAction
{
    #[Route('/forgot-password', name: 'forgot-password', methods: ['POST'])]
    public function __invoke(
        #[MapRequestPayload] ForgotPasswordRequestDTO $request,
        ForgotPasswordJsonResponder $responder,
        NotificationInterface $notification,
        NotifierInterface $notifier,
        TranslatorInterface $translator,
    ): ForgotPasswordJsonResponder {
        try {
            $note = new Notification(
                $translator->trans('Please reset your password'),
                'Click the link below to reset your password. <h1>Hi!</h1>',
            );

            $notification->send(
                $note,
                new Recipient((string)$request->email()),
            );

            return $responder->success($translator->trans('Letter sent. Check your email.'))->respond();
        } catch (\Throwable $exception) {
            return $responder->error($exception)->respond();
        }
    }
}
