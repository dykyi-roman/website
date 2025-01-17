<?php

declare(strict_types=1);

namespace Profile\Setting\DomainModel\Event;

use EventStorage\DomainModel\Event\PersistingEventInterface;
use Profile\Setting\DomainModel\ValueObject\Property;
use Profile\User\DomainModel\Enum\UserId;

final readonly class SettingIsChangedEvent implements PersistingEventInterface
{
    public function __construct(
        public UserId $id,
        public Property $property,
    ) {
    }

    public function getOccurredOn(): \DateTimeImmutable
    {
        return new \DateTimeImmutable();
    }

    public function getAggregateId(): string
    {
        return $this->id->toRfc4122();
    }

    public function getAggregateType(): string
    {
        return 'Setting.settings.changed';
    }

    /** @return array<string, string> */
    public function getPayload(): array
    {
        return [
            'category' => $this->property->category->value,
            'name' => $this->property->name->value,
            'value' => $this->property->value(),
        ];
    }

    public function getVersion(): int
    {
        return 1;
    }

    public function getPriority(): int
    {
        return 0;
    }
}
