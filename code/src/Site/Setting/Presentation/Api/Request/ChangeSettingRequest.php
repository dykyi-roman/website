<?php

declare(strict_types=1);

namespace Site\Setting\Presentation\Api\Request;

use Site\Setting\DomainModel\Enum\PropertyCategory;
use Site\Setting\DomainModel\Enum\PropertyName;
use Site\Setting\DomainModel\ValueObject\Property;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class ChangeSettingRequest
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
