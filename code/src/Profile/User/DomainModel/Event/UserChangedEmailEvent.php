<?php

declare(strict_types=1);

namespace Profile\User\DomainModel\Event;

use EventStorage\DomainModel\Event\PersistingEventInterface;
use Profile\User\DomainModel\Enum\UserId;
use Shared\DomainModel\ValueObject\Email;

final readonly class UserChangedEmailEvent implements PersistingEventInterface
{
    public function __construct(
        public UserId $id,
        public Email $oldEmail,
        public Email $newEmail,
    ) {
    }

    public function getAggregateId(): string
    {
        return $this->id->toRfc4122();
    }

    public function getAggregateType(): string
    {
        return 'user.change.email';
    }

    public function getOccurredOn(): \DateTimeImmutable
    {
        return new \DateTimeImmutable();
    }

    /** @return array<string, string> */
    public function getPayload(): array
    {
        return [
            'from' => $this->oldEmail->value,
            'to' => $this->newEmail->value,
        ];
    }

    public function getVersion(): int
    {
        return 1;
    }
}
