<?php

declare(strict_types=1);

namespace Site\Profile\Presentation\Api\Request;

use Site\Profile\DomainModel\Enum\PropertyGroup;
use Site\Profile\DomainModel\Enum\PropertyName;
use Site\Profile\DomainModel\Enum\PropertyType;
use Site\Profile\DomainModel\ValueObject\Property;
use Symfony\Component\Validator\Constraints as Assert;

final class ChangeProfileSettingRequest
{
    public function __construct(
        #[Assert\NotBlank(message: 'Group should not be blank')]
        #[Assert\Choice(callback: [PropertyGroup::class, 'cases'], message: 'Invalid group value')]
        private readonly string $group,

        #[Assert\NotBlank(message: 'Name should not be blank')]
        #[Assert\Choice(callback: [PropertyName::class, 'cases'], message: 'Invalid name value')]
        private readonly string $name,

        #[Assert\NotBlank(message: 'Type should not be blank')]
        #[Assert\Choice(callback: [PropertyType::class, 'cases'], message: 'Invalid type value')]
        private readonly string $type,

        #[Assert\NotNull(message: 'Value should not be null')]
        private readonly mixed $value,
    ) {
    }

    public function property(): Property
    {
        $type = PropertyType::from($this->type);
        $value = match($type) {
            PropertyType::STRING => (string) $this->value,
            PropertyType::INTEGER => (int) $this->value,
            PropertyType::BOOL => (bool) $this->value,
            PropertyType::DATE => $this->value instanceof \DateTimeInterface
                ? $this->value
                : new \DateTimeImmutable((string) $this->value),
        };

        return new Property(
            PropertyGroup::from($this->group),
            $type,
            PropertyName::from($this->name),
            $value
        );
    }
}
