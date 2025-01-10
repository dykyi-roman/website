<?php

declare(strict_types=1);

namespace Site\User\DomainModel\Event;

use EventStorage\DomainModel\Event\PersistingEventInterface;
use Shared\DomainModel\ValueObject\Email;
use Site\User\DomainModel\Enum\UserId;

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
            'from' => $this->oldEmail,
            'to' => $this->newEmail,
        ];
    }

    public function getVersion(): int
    {
        return 1;
    }
}
