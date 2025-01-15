<?php

declare(strict_types=1);

namespace Site\Registration\Tests\Unit\Application\ResetPassword\Query;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Site\Registration\Application\ResetPassword\Query\ResetPasswordQuery;
use Site\Registration\Application\ResetPassword\Query\ResetPasswordQueryHandler;
use Site\Registration\Application\ResetPassword\ValueObject\ResetPasswordResponse;
use Site\Registration\DomainModel\Exception\InvalidPasswordException;
use Site\Registration\DomainModel\Exception\PasswordIsNotMatchException;
use Site\Registration\DomainModel\Exception\TokenExpiredException;
use Site\Registration\DomainModel\Service\ResetPasswordService;
use Symfony\Contracts\Translation\TranslatorInterface;

#[CoversClass(ResetPasswordQueryHandler::class)]
final class ResetPasswordQueryHandlerTest extends TestCase
{
    private ResetPasswordQueryHandler $handler;
    private ResetPasswordService&MockObject $resetPasswordService;
    private TranslatorInterface&MockObject $translator;
    private LoggerInterface&MockObject $logger;

    protected function setUp(): void
    {
        $this->resetPasswordService = $this->createMock(ResetPasswordService::class);
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->handler = new ResetPasswordQueryHandler(
            $this->resetPasswordService,
            $this->translator,
            $this->logger
        );
    }

    public function testSuccessfulPasswordReset(): void
    {
        $query = new ResetPasswordQuery(
            'newPassword123',
            'newPassword123',
            'valid_token'
        );

        $this->translator->expects($this->once())
            ->method('trans')
            ->with('password_reset_success')
            ->willReturn('Password has been reset successfully');

        $this->resetPasswordService->expects($this->once())
            ->method('reset')
            ->with('valid_token', 'newPassword123');

        $response = $this->handler->__invoke($query);

        $this->assertInstanceOf(ResetPasswordResponse::class, $response);
        $this->assertTrue($response->success);
        $this->assertEquals('Password has been reset successfully', $response->message);
        $this->assertEmpty($response->errors);
    }

    public function testPasswordMismatch(): void
    {
        $query = new ResetPasswordQuery(
            'password1',
            'password2',
            'valid_token'
        );

        $this->translator->expects($this->never())
            ->method('trans');

        $this->resetPasswordService->expects($this->never())
            ->method('reset');

        $this->expectException(PasswordIsNotMatchException::class);
        $this->expectExceptionMessage('Passwords do not match');

        $this->handler->__invoke($query);
    }

    public function testTokenExpired(): void
    {
        $query = new ResetPasswordQuery(
            'newPassword123',
            'newPassword123',
            'expired_token'
        );

        $this->resetPasswordService->expects($this->once())
            ->method('reset')
            ->with('expired_token', 'newPassword123')
            ->willThrowException(new TokenExpiredException('Token has expired'));

        $this->translator->expects($this->once())
            ->method('trans')
            ->with('reset_token_expired')
            ->willReturn('Reset token has expired');

        $response = $this->handler->__invoke($query);

        $this->assertInstanceOf(ResetPasswordResponse::class, $response);
        $this->assertFalse($response->success);
        $this->assertEquals('Reset token has expired', $response->message);
        $this->assertEquals(['token' => 'Token has expired'], $response->errors);
    }

    public function testInvalidPassword(): void
    {
        $query = new ResetPasswordQuery(
            'weak',
            'weak',
            'valid_token'
        );

        $this->resetPasswordService->expects($this->once())
            ->method('reset')
            ->with('valid_token', 'weak')
            ->willThrowException(new InvalidPasswordException('Password is too weak'));

        $this->translator->expects($this->once())
            ->method('trans')
            ->with('invalid_password')
            ->willReturn('Invalid password provided');

        $response = $this->handler->__invoke($query);

        $this->assertInstanceOf(ResetPasswordResponse::class, $response);
        $this->assertFalse($response->success);
        $this->assertEquals('Invalid password provided', $response->message);
        $this->assertEquals(['password' => 'Password is too weak'], $response->errors);
    }

    public function testUnexpectedError(): void
    {
        $query = new ResetPasswordQuery(
            'newPassword123',
            'newPassword123',
            'valid_token'
        );

        $exception = new \RuntimeException('Unexpected error occurred');

        $this->resetPasswordService->expects($this->once())
            ->method('reset')
            ->with('valid_token', 'newPassword123')
            ->willThrowException($exception);

        $this->translator->expects($this->once())
            ->method('trans')
            ->with('unexpected_reset_error')
            ->willReturn('An unexpected error occurred');

        $this->logger->expects($this->once())
            ->method('error')
            ->with('Unexpected error occurred');

        $response = $this->handler->__invoke($query);

        $this->assertInstanceOf(ResetPasswordResponse::class, $response);
        $this->assertFalse($response->success);
        $this->assertEquals('An unexpected error occurred', $response->message);
        $this->assertEquals(['general' => 'Unexpected error occurred'], $response->errors);
    }
}
