<?php

declare(strict_types=1);

namespace App\Shared\DomainModel\Notification;

use App\Shared\DomainModel\ValueObject\Email;
use App\Shared\Infrastructure\Notification\EmailRecipientInterface;

final readonly class EmailNotification implements EmailRecipientInterface
{
    public function __construct(
        private Email $email,
    ) {
    }

    public function getEmail(): string
    {
        return $this->email->value;
    }
}