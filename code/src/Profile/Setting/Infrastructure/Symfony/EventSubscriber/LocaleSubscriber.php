<?php

declare(strict_types=1);

namespace Profile\Setting\Infrastructure\Symfony\EventSubscriber;

use Profile\Setting\DomainModel\Enum\PropertyName;
use Profile\Setting\DomainModel\Repository\SettingRepositoryInterface;
use Profile\User\DomainModel\Exception\AuthenticationException;
use Profile\User\DomainModel\Service\UserFetcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Translation\LocaleSwitcher;

final readonly class LocaleSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private LocaleSwitcher $localeSwitcher,
        private SettingRepositoryInterface $settingRepository,
        private UserFetcherInterface $userFetcher,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [['onKernelRequest', -8]],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        if ($request->query->get('lang')) {
            return;
        }

        try {
            $user = $this->userFetcher->fetch();
        } catch (AuthenticationException) {
            return;
        }

        $setting = $this->settingRepository->findByName($user->id(), PropertyName::SETTINGS_GENERAL_LANGUAGE);
        if (null === $setting) {
            return;
        }

        $locale = $setting->getProperty()->value();
        $this->localeSwitcher->setLocale($locale);
        $request->cookies->set('locale', $locale);
        $request->setLocale($locale);
    }
}
