<?php

declare(strict_types=1);

namespace Site\Profile\Presentation\Api\Request;

use Site\Profile\DomainModel\Enum\PropertyCategory;
use Site\Profile\DomainModel\Enum\PropertyName;
use Site\Profile\DomainModel\ValueObject\Property;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class ChangeProfileSettingRequest
{
    public function __construct(
        #[Assert\NotBlank(message: 'Category should not be blank')]
        #[Assert\Choice(callback: [PropertyCategory::class, 'values'], message: 'Invalid category value')]
        private string $category,

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
            PropertyCategory::from($this->category),
            PropertyName::from($this->name),
            $this->value
        );
    }
}
