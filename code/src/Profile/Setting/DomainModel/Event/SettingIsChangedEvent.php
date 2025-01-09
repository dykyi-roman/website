<?php

declare(strict_types=1);

namespace Profile\Setting\DomainModel\Event;

use EventStorage\DomainModel\Event\PersistingEventInterface;
use Profile\Setting\DomainModel\ValueObject\Property;
use Site\User\DomainModel\Enum\UserId;

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
        return $this->property->jsonSerialize();
    }

    public function getVersion(): int
    {
        return 1;
    }
}
