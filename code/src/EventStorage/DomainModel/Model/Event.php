<?php

declare(strict_types=1);

namespace EventStorage\DomainModel\Model;

use Doctrine\ORM\Mapping as ORM;
use EventStorage\DomainModel\Enum\EventId;

#[ORM\Entity]
#[ORM\Table(name: 'events')]
#[ORM\UniqueConstraint(
    name: 'unique_event_type_model',
    columns: ['model_id', 'type', 'occurred_on']
)]
#[ORM\Index(name: 'idx_priority_occurred', columns: ['priority', 'occurred_on'])]
class Event
{
    #[ORM\Id]
    #[ORM\Column(type: 'event_id', unique: true)]
    private EventId $id;

    #[ORM\Column(name: 'model_id', type: 'string')]
    private string $modelId;

    #[ORM\Column(type: 'string')]
    private string $type;

    /** @var array<string, mixed> */
    #[ORM\Column(type: 'json')]
    private array $payload;

    #[ORM\Column(name: 'occurred_on', type: 'datetime_immutable')]
    private \DateTimeImmutable $occurredOn;

    #[ORM\Column(type: 'smallint')]
    private int $version;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'smallint')]
    private int $priority = 0;

    #[ORM\Column(type: 'boolean')]
    private bool $archived = false;

    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(
        EventId $id,
        string $modelId,
        string $type,
        array $payload,
        \DateTimeImmutable $occurredOn,
        int $version,
        int $priority = 0,
    ) {
        $this->id = $id;
        $this->modelId = $modelId;
        $this->type = $type;
        $this->payload = $payload;
        $this->occurredOn = $occurredOn;
        $this->version = $version;
        $this->createdAt = new \DateTimeImmutable();
        $this->priority = $priority;
    }

    public function getId(): EventId
    {
        return $this->id;
    }

    public function getModelId(): string
    {
        return $this->modelId;
    }

    public function getType(): string
    {
        return $this->type;
    }

    /** @return array<string, mixed> */
    public function getPayload(): array
    {
        return $this->payload;
    }

    public function getOccurredOn(): \DateTimeImmutable
    {
        return $this->occurredOn;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getVersion(): int
    {
        return $this->version;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function isArchived(): bool
    {
        return $this->archived;
    }

    public function archive(): void
    {
        $this->archived = true;
    }
}
