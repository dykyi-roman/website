<?php

declare(strict_types=1);

namespace Site\Registration\Tests\Unit\DomainModel\ValueObject;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Site\Registration\DomainModel\ValueObject\ResetPasswordToken;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

#[CoversClass(ResetPasswordToken::class)]
final class ResetPasswordTokenTest extends TestCase
{
    public function testTokenGeneration(): void
    {
        $expectedToken = 'generated_token';

        /** @var TokenGeneratorInterface&\PHPUnit\Framework\MockObject\MockObject $tokenGenerator */
        $tokenGenerator = $this->createMock(TokenGeneratorInterface::class);
        $tokenGenerator->expects($this->once())
            ->method('generateToken')
            ->willReturn($expectedToken);

        $resetToken = new ResetPasswordToken($tokenGenerator);

        $this->assertSame($expectedToken, (string) $resetToken);
    }
}
