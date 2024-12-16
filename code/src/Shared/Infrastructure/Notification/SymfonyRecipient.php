<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Notification;

use Symfony\Component\Notifier\Recipient\RecipientInterface as SymfonyRecipientInterface;

final readonly class SymfonyRecipient implements SymfonyRecipientInterface
{
    public function __construct(
        private RecipientInterface $recipient,
    ) {
    }

    public function getEmail(): string
    {
        return $this->recipient->getEmail();
    }

    public function getPhone(): string
    {
        return $this->recipient->getPhone() ?? throw new \RuntimeException('Phone number is not set for SMS recipient');
    }
}
