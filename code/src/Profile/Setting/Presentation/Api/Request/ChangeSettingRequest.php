<?php

declare(strict_types=1);

namespace Profile\Setting\Presentation\Api\Request;

use Profile\Setting\DomainModel\Enum\PropertyCategory;
use Profile\Setting\DomainModel\Enum\PropertyName;
use Profile\Setting\DomainModel\ValueObject\Property;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class ChangeSettingRequest
{
    public function __construct(
        #[Assert\NotBlank(message: 'Settings should not be blank')]
        #[Assert\Type(type: 'array', message: 'Settings must be an array')]
        #[Assert\All([
            new Assert\Collection([
                'category' => [
                    new Assert\NotBlank(message: 'Category should not be blank'),
                    new Assert\Choice(callback: [PropertyCategory::class, 'values'], message: 'Invalid category value'),
                ],
                'name' => [
                    new Assert\NotBlank(message: 'Name should not be blank'),
                    new Assert\Choice(callback: [PropertyName::class, 'values'], message: 'Invalid name value'),
                ],
                'value' => [
                    new Assert\NotNull(message: 'Value should not be null'),
                ],
            ]),
        ])]
        private array $settings,
    ) {
    }

    /**
     * @return Property[]
     */
    public function properties(): array
    {
        return array_map(
            fn (array $setting) => new Property(
                PropertyCategory::from($setting['category']),
                PropertyName::from($setting['name']),
                $setting['value']
            ),
            $this->settings
        );
    }
}
