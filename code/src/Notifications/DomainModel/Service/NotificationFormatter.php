<?php

declare(strict_types=1);

namespace Notifications\DomainModel\Service;

use Notifications\DomainModel\Exception\NotificationNotFoundException;
use Notifications\DomainModel\Model\UserNotification;
use Notifications\DomainModel\Repository\NotificationRepositoryInterface;

final readonly class NotificationFormatter
{
    public function __construct(
        private NotificationRepositoryInterface $notificationRepository,
        private NotificationTranslator $notificationTranslator,
    ) {
    }

    /**
     * @throws NotificationNotFoundException
     * @return array<string, string|int|float|bool|\DateTimeInterface|null>
     */
    public function transform(UserNotification $userNotification): array
    {
        $notification = $this->notificationRepository->findById($userNotification->getNotificationId());

        return [
            ...$this->notificationTranslator->translateNotification($notification),
            ...[
                'id' => $userNotification->getId()->toRfc4122(),
                'readAt' => $userNotification->getReadAt()?->format('c'),
                'createdAt' => $userNotification->getCreatedAt()->format('c'),
                'deletedAt' => $userNotification->getDeletedAt()?->format('c'),
            ],
        ];
    }
}
