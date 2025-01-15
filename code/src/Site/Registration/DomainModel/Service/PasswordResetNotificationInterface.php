<?php

declare(strict_types=1);

namespace Site\Registration\DomainModel\Service;

interface PasswordResetNotificationInterface
{
    public function send(string $email, string $name, string $token): void;
}
