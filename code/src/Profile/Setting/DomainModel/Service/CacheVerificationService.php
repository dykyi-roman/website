<?php

declare(strict_types=1);

namespace Profile\Setting\DomainModel\Service;

use Profile\Setting\DomainModel\Enum\VerificationType;
use Profile\Setting\DomainModel\ValueObject\VerificationCode;
use Psr\SimpleCache\CacheInterface;

final readonly class CacheVerificationService implements VerificationService
{
    public function __construct(
        private CacheInterface $cache,
        private int $verificationCodeTtl,
    ) {
    }

    public function generateCode(string $userId, VerificationType $type): VerificationCode
    {
        $code = VerificationCode::generate();
        $this->cache->set(
            $this->generateCacheKey($userId, $type),
            $code->toString(),
            $this->verificationCodeTtl
        );

        return $code;
    }

    public function verifyCode(string $userId, VerificationType $type, VerificationCode $code): bool
    {
        $storedCode = $this->cache->get($this->generateCacheKey($userId, $type));

        if (!is_string($storedCode)) {
            return false;
        }

        return $code->equals(VerificationCode::fromString($storedCode));
    }

    public function invalidateCode(string $userId, VerificationType $type): void
    {
        $this->cache->delete($this->generateCacheKey($userId, $type));
    }

    private function generateCacheKey(string $userId, VerificationType $type): string
    {
        return sprintf('verification_code_%s_%s', $userId, $type->value);
    }
}
