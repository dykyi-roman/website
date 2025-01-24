<?php

declare(strict_types=1);

namespace Profile\Setting\Application\SettingsAccount\Command;

use Profile\Setting\DomainModel\Enum\VerificationType;
use Profile\Setting\DomainModel\Exception\VerificationException;
use Profile\Setting\DomainModel\Service\VerificationService;
use Profile\Setting\DomainModel\ValueObject\VerificationCode;
use Profile\User\Application\UserVerification\Command\VerifyUserEmailCommand;
use Profile\User\Application\UserVerification\Command\VerifyUserPhoneCommand;
use Psr\Log\LoggerInterface;
use Shared\DomainModel\Services\MessageBusInterface;
use Shared\DomainModel\ValueObject\UserId;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class VerifyCodeHandler
{
    public function __construct(
        private VerificationService $verificationService,
        private MessageBusInterface $messageBus,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(VerifyCodeCommand $command): void
    {
        try {
            $type = VerificationType::from($command->type);
            $code = VerificationCode::fromString($command->code);

            if (!$this->verificationService->verifyCode($command->userId->toRfc4122(), $type, $code)) {
                throw VerificationException::invalidCode();
            }

            $this->dispatchVerificationCommand($command->userId, $type);
            $this->verificationService->invalidateCode($command->userId->toRfc4122(), $type);

            $this->logger->info('Verification completed successfully', [
                'userId' => $command->userId->toRfc4122(),
                'type' => $type->value,
            ]);
        } catch (VerificationException $exception) {
            $this->logger->error('Verification failed', [
                'userId' => $command->userId->toRfc4122(),
                'type' => $command->type,
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    /**
     * @throws \Throwable
     */
    private function dispatchVerificationCommand(UserId $userId, VerificationType $type): void
    {
        $command = match ($type) {
            VerificationType::EMAIL => new VerifyUserEmailCommand($userId),
            VerificationType::PHONE => new VerifyUserPhoneCommand($userId),
        };

        $this->messageBus->dispatch($command);
    }
}
