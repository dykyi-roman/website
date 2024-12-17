<?php

namespace App\Shared\Infrastructure\Notification;

use Symfony\Component\Notifier\Exception\InvalidArgumentException;

readonly class Recipient implements EmailRecipientInterface, SmsRecipientInterface
{
    public function __construct(
        private string $email = '',
        private string $phone = ''
    ) {
        if ('' === $email && '' === $phone) {
            throw new InvalidArgumentException(
                sprintf('"%s" needs an email or a phone but both cannot be empty.', static::class)
            );
        }
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }
}
