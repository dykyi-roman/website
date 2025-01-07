<?php

declare(strict_types=1);

namespace Site\Registration\DomainModel\Event;

use EventStorage\DomainModel\Event\PersistingEventInterface;
use Shared\DomainModel\ValueObject\Email;
use Site\User\DomainModel\Enum\UserId;

final readonly class UserLoggedInEvent implements PersistingEventInterface
{
    public function __construct(
        public UserId $id,
        public Email $email,
        public string $type,
    ) {
    }

    public function getAggregateId(): string
    {
        return $this->id->toRfc4122();
    }

    public function getAggregateType(): string
    {
        return 'user.logged.'.$this->type;
    }

    public function getOccurredOn(): \DateTimeImmutable
    {
        return new \DateTimeImmutable();
    }

    /** @return array<string, string> */
    public function getPayload(): array
    {
        return [
            'email' => $this->email->value,
        ];
    }

    public function getVersion(): int
    {
        return 1;
    }
}
