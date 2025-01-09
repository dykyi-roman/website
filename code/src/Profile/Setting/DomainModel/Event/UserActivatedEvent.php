<?php

declare(strict_types=1);

namespace Profile\Setting\DomainModel\Event;

use EventStorage\DomainModel\Event\PersistingEventInterface;
use Shared\DomainModel\ValueObject\Email;
use Site\User\DomainModel\Enum\UserId;

final readonly class UserActivatedEvent implements PersistingEventInterface
{
    public function __construct(
        public UserId $id,
        public Email $email,
        public string $name,
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
        return 'user.activated';
    }

    /** @return array<string, string> */
    public function getPayload(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email->value,
        ];
    }

    public function getVersion(): int
    {
        return 1;
    }
}
