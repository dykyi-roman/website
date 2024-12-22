<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Notification;

interface EmailRecipientInterface extends RecipientInterface
{
    public function getEmail(): string;
}
