<?php

declare(strict_types=1);

namespace Profile\Setting\Infrastructure\Symfony\EventSubscriber;

use Profile\Setting\DomainModel\Enum\PropertyName;
use Profile\Setting\DomainModel\Repository\SettingRepositoryInterface;
use Site\Locale\DomainModel\Service\LocaleResolverInterface;
use Site\User\DomainModel\Exception\AuthenticationException;
use Site\User\DomainModel\Service\UserFetcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Translation\LocaleSwitcher;

final readonly class LocaleSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private LocaleSwitcher $localeSwitcher,
        private UserFetcher $userFetcher,
        private SettingRepositoryInterface $settingRepository,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [['onKernelRequest', 30]],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        try {
            $user = $this->userFetcher->fetch();
            $setting = $this->settingRepository->findByName($user->getId(), PropertyName::SETTINGS_GENERAL_LANGUAGE);
            if (null === $setting) {
                return;
            }

            $locale = $setting->getProperty()->value;
            $this->localeSwitcher->setLocale($locale);
            $request->cookies->set('locale', $locale);
            $request->setLocale($locale);
        } catch (AuthenticationException) {
            return;
        }
    }
}
