<?php

declare(strict_types=1);

namespace App\Registration\DomainModel\Service;

use App\Shared\DomainModel\ValueObject\Email;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

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
        $this->cache->get($key, function (ItemInterface $item) use ($emailSendAction) {
            $emailSendAction();
            $item->expiresAfter(self::RATE_LIMIT_DURATION);

            return true;
        });
    }

    private function generateCacheKey(string $email): string
    {
        return self::CACHE_PREFIX . hash('sha256', $email);
    }
}
