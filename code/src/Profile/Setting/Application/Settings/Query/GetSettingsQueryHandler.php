<?php

declare(strict_types=1);

namespace Profile\Setting\Application\Settings\Query;

use Profile\Setting\DomainModel\Repository\SettingRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GetSettingsQueryHandler
{
    public function __construct(
        private SettingRepositoryInterface $settingRepository,
    ) {
    }

    /**
     * @return array<string, array<string, string>
     */
    public function __invoke(GetSettingsQuery $query): array
    {
        $settings = $this->settingRepository->findAll($query->userId);

        $result = [];
        foreach ($settings as $setting) {
            $property = $setting->getProperty();
            if (!isset($result[$property->category->value])) {
                $result[$property->category->value] = [];
            }
            $result[strtolower($property->category->value)][$property->name->value] = $property->toString($property->value);
        }

        return $result;
    }
}
