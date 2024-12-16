<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Notification;

interface SmsRecipientInterface extends RecipientInterface
{
    public function getPhone(): string;
}
