<?php

declare(strict_types=1);

namespace Profile\Setting\Tests\Unit\Application\Settings\Query;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Profile\Setting\Application\Settings\Query\GetSettingsQuery;
use Profile\Setting\Application\Settings\Query\GetSettingsQueryHandler;
use Profile\Setting\DomainModel\Enum\PropertyCategory;
use Profile\Setting\DomainModel\Enum\PropertyName;
use Profile\Setting\DomainModel\Model\Setting;
use Profile\Setting\DomainModel\Repository\SettingRepositoryInterface;
use Profile\Setting\DomainModel\ValueObject\Property;
use Shared\DomainModel\ValueObject\UserId;

#[CoversClass(GetSettingsQueryHandler::class)]
final class GetSettingsQueryHandlerTest extends TestCase
{
    private MockObject&SettingRepositoryInterface $settingRepository;
    private GetSettingsQueryHandler $handler;

    protected function setUp(): void
    {
        $this->settingRepository = $this->createMock(SettingRepositoryInterface::class);
        $this->handler = new GetSettingsQueryHandler($this->settingRepository);
    }

    public function testSuccessfulSettingsRetrieval(): void
    {
        $userId = new UserId();
        $query = new GetSettingsQuery($userId);

        $notificationProperty = new Property(
            PropertyCategory::NOTIFICATION,
            PropertyName::ACCEPTED_COOKIES,
            'true'
        );

        $generalProperty = new Property(
            PropertyCategory::GENERAL,
            PropertyName::SETTINGS_GENERAL_THEME,
            'dark'
        );

        $setting1 = $this->createMock(Setting::class);
        $setting1->method('getProperty')->willReturn($notificationProperty);

        $setting2 = $this->createMock(Setting::class);
        $setting2->method('getProperty')->willReturn($generalProperty);

        $this->settingRepository
            ->expects(self::once())
            ->method('findAll')
            ->with($userId)
            ->willReturn([$setting1, $setting2]);

        $expected = [
            'notification' => ['accepted_cookies' => 'true'],
            'general' => ['theme' => 'dark'],
        ];

        $result = $this->handler->__invoke($query);

        self::assertEquals($expected, $result);
    }

    public function testEmptySettingsRetrieval(): void
    {
        $userId = new UserId();
        $query = new GetSettingsQuery($userId);

        $this->settingRepository
            ->expects(self::once())
            ->method('findAll')
            ->with($userId)
            ->willReturn([]);

        $result = $this->handler->__invoke($query);

        self::assertEmpty($result);
    }
}
