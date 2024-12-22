<?php

declare(strict_types=1);

namespace Site\Registration\DomainModel\Service;

use Psr\SimpleCache\CacheInterface;
use Shared\DomainModel\ValueObject\Email;

final readonly class PasswordResetRateLimiterService
{
    private const string CACHE_PREFIX = 'forgot_password_';
    private const int RATE_LIMIT_DURATION = 3600; // 1 hour

    public function __construct(
        private CacheInterface $cache,
    ) {
    }

    public function tryPasswordResetEmail(Email $email, callable $emailSendAction): void
    {
        $key = $this->generateCacheKey($email->hash());
        if (null === $this->cache->get($key)) {
            $emailSendAction();
            $this->cache->set($key, 1, self::RATE_LIMIT_DURATION);
        }
    }

    private function generateCacheKey(string $email): string
    {
        return self::CACHE_PREFIX.$email;
    }
}
