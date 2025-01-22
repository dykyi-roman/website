<?php

declare(strict_types=1);

namespace Notifications\DomainModel\Service;

use Notifications\DomainModel\Model\Notification;
use Notifications\DomainModel\Model\TranslatableText;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class NotificationTranslator
{
    public function __construct(
        private TranslatorInterface $translator,
    ) {
    }

    /**
     * @return array{
     *     type: string,
     *     title: string,
     *     message: string,
     *     icon: string|null
     * }
     */
    public function translateNotification(Notification $notification): array
    {
        return [
            'type' => $notification->getType()->value,
            'title' => $this->translateText($notification->getTitle()),
            'message' => $this->translateText($notification->getMessage()),
            'icon' => $notification->getIcon(),
        ];
    }

    private function translateText(TranslatableText $text): string
    {
        return $this->translator->trans(
            $text->getMessageId(),
            $text->getParameters(),
        );
    }
}
