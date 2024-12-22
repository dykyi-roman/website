<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Notification;

interface SmsRecipientInterface extends RecipientInterface
{
    public function getPhone(): string;
}
