<?php

declare(strict_types=1);

namespace App\Locale\Infrastructure\Symfony\EventSubscriber;

use App\Locale\DomainModel\Service\LocaleResolverInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Translation\LocaleSwitcher;

final readonly class LocaleSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private string $defaultLocale,
        private array $supportedLocales,
        private LocaleSwitcher $localeSwitcher,
        private LocaleResolverInterface $localeResolver,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [['onKernelRequest', 20]],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        $locale = $this->localeResolver->resolve($request);
        $locale = in_array($locale, $this->supportedLocales, true) ? $locale : $this->defaultLocale;

        $this->localeSwitcher->setLocale($locale);
        $request->setLocale($locale);
    }
}
