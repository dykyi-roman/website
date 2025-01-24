<?php

declare(strict_types=1);

namespace Profile\Setting\Tests\Unit\DomainModel\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Profile\Setting\DomainModel\Enum\VerificationType;
use Profile\Setting\DomainModel\Service\CacheVerificationService;
use Profile\Setting\DomainModel\ValueObject\VerificationCode;
use Psr\SimpleCache\CacheInterface;
use Shared\DomainModel\ValueObject\UserId;
use Symfony\Component\Uid\Uuid;

#[CoversClass(CacheVerificationService::class)]
final class CacheVerificationServiceTest extends TestCase
{
    private const int TTL = 300; // 5 minutes
    private MockObject&CacheInterface $cache;
    private CacheVerificationService $service;

    protected function setUp(): void
    {
        $this->cache = $this->createMock(CacheInterface::class);
        $this->service = new CacheVerificationService($this->cache, self::TTL);
    }

    public function testGenerateCode(): void
    {
        $userId = (new UserId())->toRfc4122();
        $type = VerificationType::EMAIL;
        $cacheKey = sprintf('verification_code_%s_%s', $userId, $type->value);

        $this->cache
            ->expects(self::once())
            ->method('set')
            ->with(
                $cacheKey,
                self::callback(fn (string $code) => 6 === strlen($code) && is_numeric($code)),
                self::TTL
            )
            ->willReturn(true);

        $code = $this->service->generateCode($userId, $type);

        self::assertInstanceOf(VerificationCode::class, $code);
        self::assertSame(6, strlen($code->toString()));
        self::assertTrue(is_numeric($code->toString()));
    }

    public function testVerifyCodeSuccess(): void
    {
        $userId = Uuid::v4()->toRfc4122();
        $type = VerificationType::EMAIL;
        $cacheKey = sprintf('verification_code_%s_%s', $userId, $type->value);
        $storedCode = '123456';
        $verificationCode = VerificationCode::fromString($storedCode);

        $this->cache
            ->expects(self::once())
            ->method('get')
            ->with($cacheKey)
            ->willReturn($storedCode);

        $result = $this->service->verifyCode($userId, $type, $verificationCode);

        self::assertTrue($result);
    }

    public function testVerifyCodeFailureWhenCodeDoesNotMatch(): void
    {
        $userId = Uuid::v4()->toRfc4122();
        $type = VerificationType::EMAIL;
        $cacheKey = sprintf('verification_code_%s_%s', $userId, $type->value);
        $storedCode = '123456';
        $verificationCode = VerificationCode::fromString('654321');

        $this->cache
            ->expects(self::once())
            ->method('get')
            ->with($cacheKey)
            ->willReturn($storedCode);

        $result = $this->service->verifyCode($userId, $type, $verificationCode);

        self::assertFalse($result);
    }

    public function testVerifyCodeFailureWhenCodeNotFound(): void
    {
        $userId = Uuid::v4()->toRfc4122();
        $type = VerificationType::EMAIL;
        $cacheKey = sprintf('verification_code_%s_%s', $userId, $type->value);
        $verificationCode = VerificationCode::fromString('123456');

        $this->cache
            ->expects(self::once())
            ->method('get')
            ->with($cacheKey)
            ->willReturn(null);

        $result = $this->service->verifyCode($userId, $type, $verificationCode);

        self::assertFalse($result);
    }

    public function testInvalidateCode(): void
    {
        $userId = Uuid::v4()->toRfc4122();
        $type = VerificationType::EMAIL;
        $cacheKey = sprintf('verification_code_%s_%s', $userId, $type->value);

        $this->cache
            ->expects(self::once())
            ->method('delete')
            ->with($cacheKey)
            ->willReturn(true);

        $this->service->invalidateCode($userId, $type);
    }
}
