<?php

declare(strict_types=1);

namespace Site\Profile\DomainModel\Model;

use Doctrine\ORM\Mapping as ORM;
use Site\Profile\DomainModel\Enum\PropertyGroup;
use Site\Profile\DomainModel\Enum\PropertyName;
use Site\Profile\DomainModel\Enum\PropertyType;
use Site\Profile\DomainModel\ValueObject\Property;
use Site\User\DomainModel\Enum\UserId;

#[ORM\Entity]
#[ORM\Table(name: 'profile')]
#[ORM\HasLifecycleCallbacks]
class Profile
{
    #[ORM\Id]
    #[ORM\Column(type: 'user_id', unique: true)]
    private UserId $id;

    #[ORM\Column(type: 'property_group', length: 100)]
    private PropertyGroup $group;

    #[ORM\Column(type: 'property_type', length: 100)]
    private PropertyType $type;

    #[ORM\Column(type: 'property_name', length: 100)]
    private PropertyName $name;

    #[ORM\Column(type: 'string', nullable: true)]
    private string $value;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetime_immutable')]
    private \DateTimeImmutable $updatedAt;

    public function __construct(
        UserId $id,
        Property $property,
    ) {
        $this->id = $id;
        $this->group = $property->group;
        $this->type = $property->type;
        $this->name = $property->name;
        $this->value = match ($property->type) {
            PropertyType::STRING => $property->value,
            PropertyType::INTEGER => (string) $property->value,
            PropertyType::BOOL => $property->value ? '1' : '0',
            PropertyType::DATE => $property->value instanceof \DateTimeInterface ? $property->value->format('Y-m-d H:i:s') : throw new \InvalidArgumentException('Invalid date value'),
        };
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): UserId
    {
        return $this->id;
    }

    public function getProperty(): Property
    {
        return new Property(
            $this->group,
            $this->type,
            $this->name,
            $this->value,
        );
    }

    #[ORM\PreUpdate]
    public function updateTimestamp(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
