<?php

declare(strict_types=1);

namespace Profile\Setting\DomainModel\Service;

use Profile\Setting\DomainModel\Enum\VerificationType;
use Profile\Setting\DomainModel\ValueObject\VerificationCode;
use Shared\DomainModel\Services\NotificationInterface;
use Shared\DomainModel\ValueObject\Notification;
use Shared\DomainModel\ValueObject\Recipient;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

final readonly class SendNotificationService
{
    public function __construct(
        private NotificationInterface $notification,
        private Environment $twig,
        private TranslatorInterface $translator,
        private int $verificationCodeTtl,
    ) {
    }

    public function send(VerificationType $type, string $recipient, VerificationCode $code): void
    {
        match ($type) {
            VerificationType::EMAIL => $this->sendEmail($recipient, $code->toString()),
            VerificationType::PHONE => $this->sendSms($recipient, $code->toString()),
        };
    }

    private function sendEmail(string $recipient, string $code): void
    {
        $html = $this->twig->render('@Setting/email/verification-code.html.twig', [
            'code' => $code,
            'title' => $this->translator->trans('settings.account.verification_code_title'),
            'message' => $this->translator->trans('settings.account.verification_code_message'),
            'expiry' => $this->translator->trans(
                'settings.account.verification_code_expiry',
                ['%minutes%' => $this->verificationCodeTtl / 60]
            ),
            'ignore' => $this->translator->trans('settings.account.verification_code_ignore'),
        ]);

        $this->notification->send(
            new Notification(
                $this->translator->trans('settings.account.verification_code_title'),
                $html,
                ['custom-email']
            ),
            new Recipient($recipient)
        );
    }

    private function sendSms(string $recipient, string $code): void
    {
        $this->notification->send(
            new Notification(
                $this->translator->trans('settings.account.verification_code_title'),
                sprintf('%s: %s', $this->translator->trans('settings.account.your_verification_code'), $code),
                ['sms'],
            ),
            new Recipient('null@domain.com', $recipient),
        );
    }
}
