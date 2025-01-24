<?php

declare(strict_types=1);

namespace Notifications\DomainModel\Service;

use Notifications\DomainModel\Exception\NotificationNotFoundException;
use Notifications\DomainModel\Model\UserNotification;

final readonly class NotificationFormatter
{
    public function __construct(
        private NotificationTranslatorInterface $notificationTranslator,
    ) {
    }

    /**
     * @return array<string, string|int|float|bool|\DateTimeInterface|null>
     *
     * @throws NotificationNotFoundException
     */
    public function transform(UserNotification $userNotification): array
    {
        return [
            ...$this->notificationTranslator->translateNotification($userNotification->notification()),
            ...[
                'id' => $userNotification->getId()->toRfc4122(),
                'readAt' => $userNotification->getReadAt()?->format('c'),
                'createdAt' => $userNotification->getCreatedAt()->format('c'),
                'deletedAt' => $userNotification->getDeletedAt()?->format('c'),
            ],
        ];
    }
}
