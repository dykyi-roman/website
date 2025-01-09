<?php

declare(strict_types=1);

namespace Site\Locale\Infrastructure\Symfony\EventSubscriber;

use Site\Locale\DomainModel\Service\LocaleResolverInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Translation\LocaleSwitcher;

final readonly class LocaleSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private string $defaultLocale,
        /** @var string[] */
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

        $resolvedLocale = $this->localeResolver->resolve($request) ?? $this->defaultLocale;
        $locale = in_array($resolvedLocale, $this->supportedLocales, true) ? $resolvedLocale : $this->defaultLocale;

        // At this point $locale is guaranteed to be a non-null string from $supportedLocales or $defaultLocale
        $this->localeSwitcher->setLocale($locale);
        $request->cookies->set('locale', $locale);
        $request->setLocale($locale);
    }
}
