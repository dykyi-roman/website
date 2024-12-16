<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Notification;

interface EmailRecipientInterface extends RecipientInterface
{
    public function getEmail(): string;
}
