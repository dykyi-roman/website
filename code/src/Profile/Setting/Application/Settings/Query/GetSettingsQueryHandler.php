<?php

declare(strict_types=1);

namespace Profile\Setting\Application\Settings\Query;

use Profile\Setting\DomainModel\Repository\SettingRepositoryInterface;
use Profile\Setting\DomainModel\ValueObject\Property;

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
        $properties = $this->settingRepository->findAll($query->userId);

        $result = [];
        foreach ($properties as $property) {
            $result[$property->category->value] = [$property->name->value => $property->toString($property->value)];
        }

        return $result;
    }
}
