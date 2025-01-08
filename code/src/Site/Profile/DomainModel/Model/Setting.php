<?php

declare(strict_types=1);

namespace Site\Profile\DomainModel\Model;

use Doctrine\ORM\Mapping as ORM;
use Shared\DomainModel\Model\AbstractDomainModel;
use Site\Profile\DomainModel\Enum\PropertyCategory;
use Site\Profile\DomainModel\Enum\PropertyName;
use Site\Profile\DomainModel\Enum\SettingId;
use Site\Profile\DomainModel\Event\ProfileSettingsIsChangedEvent;
use Site\Profile\DomainModel\ValueObject\Property;
use Site\User\DomainModel\Enum\UserId;

#[ORM\Entity]
#[ORM\Table(name: 'settings')]
#[ORM\HasLifecycleCallbacks]
class Setting extends AbstractDomainModel
{
    #[ORM\Id]
    #[ORM\Column(type: 'setting_id', unique: true)]
    private SettingId $id;

    #[ORM\Column(name: 'user_id', type: 'user_id', unique: true)]
    private UserId $userId;

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
        SettingId $id,
        UserId $userId,
        Property $property,
    ) {
        $this->id = $id;
        $this->userId = $userId;
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

        $this->raise(new ProfileSettingsIsChangedEvent($this->userId, $property));
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
