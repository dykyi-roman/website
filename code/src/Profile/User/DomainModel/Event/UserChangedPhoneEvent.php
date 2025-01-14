<?php

declare(strict_types=1);

namespace Profile\User\DomainModel\Event;

use EventStorage\DomainModel\Event\PersistingEventInterface;
use Profile\User\DomainModel\Enum\UserId;

final readonly class UserChangedPhoneEvent implements PersistingEventInterface
{
    public function __construct(
        public UserId $id,
        public ?string $oldPhone,
        public string $newPhone,
    ) {
    }

    public function getAggregateId(): string
    {
        return $this->id->toRfc4122();
    }

    public function getAggregateType(): string
    {
        return 'user.change.phone';
    }

    public function getOccurredOn(): \DateTimeImmutable
    {
        return new \DateTimeImmutable();
    }

    /** @return array<string, string> */
    public function getPayload(): array
    {
        return [
            'from' => $this->oldPhone ?? '',
            'to' => $this->newPhone,
        ];
    }

    public function getVersion(): int
    {
        return 1;
    }
}
