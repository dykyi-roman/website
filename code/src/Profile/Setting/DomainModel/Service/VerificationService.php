<?php

declare(strict_types=1);

namespace Profile\Setting\DomainModel\Service;

use Profile\Setting\DomainModel\Enum\VerificationType;
use Profile\Setting\DomainModel\ValueObject\VerificationCode;

interface VerificationService
{
    public function generateCode(string $userId, VerificationType $type): VerificationCode;

    public function verifyCode(string $userId, VerificationType $type, VerificationCode $code): bool;

    public function invalidateCode(string $userId, VerificationType $type): void;
}
