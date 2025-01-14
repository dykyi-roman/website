<?php

declare(strict_types=1);

namespace Profile\Setting\Application\SettingsAccount\Command;

use Profile\Setting\DomainModel\Enum\VerificationType;
use Profile\Setting\DomainModel\Exception\VerificationException;
use Profile\Setting\DomainModel\Service\SendNotificationService;
use Profile\Setting\DomainModel\Service\VerificationService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class SendVerificationCodeHandler
{
    public function __construct(
        private VerificationService $verificationService,
        private SendNotificationService $sendNotificationService,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(SendVerificationCodeCommand $command): void
    {
        try {
            $type = VerificationType::from($command->type);
            $code = $this->verificationService->generateCode($command->userId->toRfc4122(), $type);
            $this->sendNotificationService->send($type, $command->recipient, $code);

            $this->logger->info('Verification code sent successfully', [
                'userId' => $command->userId->toRfc4122(),
                'type' => $type->value,
                'recipient' => $command->recipient,
            ]);
        } catch (VerificationException $exception) {
            $this->logger->error('Failed to send verification code', [
                'userId' => $command->userId->toRfc4122(),
                'type' => $command->type,
                'recipient' => $command->recipient,
                'error' => $exception->getMessage(),
            ]);
            throw $exception;
        }
    }
}
