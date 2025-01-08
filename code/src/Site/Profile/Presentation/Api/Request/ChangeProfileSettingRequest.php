<?php

declare(strict_types=1);

namespace Site\Profile\Presentation\Api\Request;

use Site\Profile\DomainModel\Enum\PropertyGroup;
use Site\Profile\DomainModel\Enum\PropertyName;
use Site\Profile\DomainModel\ValueObject\Property;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class ChangeProfileSettingRequest
{
    public function __construct(
        #[Assert\NotBlank(message: 'Group should not be blank')]
        #[Assert\Choice(callback: [PropertyGroup::class, 'values'], message: 'Invalid group value')]
        private string $group,

        #[Assert\NotBlank(message: 'Name should not be blank')]
        #[Assert\Choice(callback: [PropertyName::class, 'values'], message: 'Invalid name value')]
        private string $name,

        #[Assert\NotNull(message: 'Value should not be null')]
        private mixed $value,
    ) {
    }

    public function property(): Property
    {
        return new Property(
            PropertyGroup::from($this->group),
            PropertyName::from($this->name),
            $this->value
        );
    }
}
