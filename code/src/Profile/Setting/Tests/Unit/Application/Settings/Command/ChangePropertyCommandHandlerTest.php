<?php

declare(strict_types=1);

namespace Profile\Setting\Tests\Unit\Application\Settings\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Profile\Setting\Application\Settings\Command\ChangePropertyCommand;
use Profile\Setting\Application\Settings\Command\ChangePropertyCommandHandler;
use Profile\Setting\DomainModel\Enum\PropertyCategory;
use Profile\Setting\DomainModel\Enum\PropertyName;
use Profile\Setting\DomainModel\Repository\SettingRepositoryInterface;
use Profile\Setting\DomainModel\ValueObject\Property;
use Psr\Log\LoggerInterface;
use Shared\DomainModel\ValueObject\UserId;

#[CoversClass(ChangePropertyCommandHandler::class)]
final class ChangePropertyCommandHandlerTest extends TestCase
{
    private MockObject&SettingRepositoryInterface $settingRepository;
    private MockObject&LoggerInterface $logger;
    private ChangePropertyCommandHandler $handler;

    protected function setUp(): void
    {
        $this->settingRepository = $this->createMock(SettingRepositoryInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->handler = new ChangePropertyCommandHandler(
            $this->settingRepository,
            $this->logger
        );
    }

    public function testSuccessfulPropertyUpdate(): void
    {
        $userId = new UserId();
        $properties = [
            new Property(
                PropertyCategory::GENERAL,
                PropertyName::SETTINGS_GENERAL_THEME,
                'dark'
            ),
        ];

        $command = new ChangePropertyCommand($userId, $properties);

        $this->settingRepository
            ->expects(self::once())
            ->method('updateProperties')
            ->with($userId, ...$properties);

        $this->logger
            ->expects(self::never())
            ->method('error');

        $this->handler->__invoke($command);
    }

    public function testHandleErrorDuringUpdate(): void
    {
        $userId = new UserId();
        $properties = [
            new Property(
                PropertyCategory::GENERAL,
                PropertyName::SETTINGS_GENERAL_THEME,
                'dark'
            ),
        ];
        $errorMessage = 'Update failed';

        $command = new ChangePropertyCommand($userId, $properties);

        $this->settingRepository
            ->expects(self::once())
            ->method('updateProperties')
            ->with($userId, ...$properties)
            ->willThrowException(new \RuntimeException($errorMessage));

        $this->logger
            ->expects(self::once())
            ->method('error')
            ->with($errorMessage);

        $this->handler->__invoke($command);
    }
}
