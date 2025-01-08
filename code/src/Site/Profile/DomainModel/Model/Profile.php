<?php

declare(strict_types=1);

namespace Site\Profile\DomainModel\Model;

use Doctrine\ORM\Mapping as ORM;
use Shared\DomainModel\Model\AbstractDomainModel;
use Site\Profile\DomainModel\Enum\PropertyCategory;
use Site\Profile\DomainModel\Enum\PropertyName;
use Site\Profile\DomainModel\Event\ProfileSettingsIsChangedEvent;
use Site\Profile\DomainModel\ValueObject\Property;
use Site\User\DomainModel\Enum\UserId;

#[ORM\Entity]
#[ORM\Table(name: 'profile')]
#[ORM\HasLifecycleCallbacks]
class Profile extends AbstractDomainModel
{
    #[ORM\Id]
    #[ORM\Column(type: 'user_id', unique: true)]
    private UserId $id;

    #[ORM\Column(name: 'category', type: 'property_category', length: 100)]
    private PropertyCategory $category;

    #[ORM\Column(name: 'name', type: 'property_name', length: 100)]
    private PropertyName $name;

    #[ORM\Column(name: 'value', type: 'string', nullable: true)]
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
        $this->category = $property->category;
        $this->name = $property->name;
        $this->value = $property->toString($property->value);
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
            $this->category,
            $this->name,
            $this->value,
        );
    }

    public function changeProperty(Property $property): void
    {
        $this->category = $property->category;
        $this->name = $property->name;
        $this->value = $property->toString($property->value);

        $this->raise(new ProfileSettingsIsChangedEvent($this->id, $property));
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
