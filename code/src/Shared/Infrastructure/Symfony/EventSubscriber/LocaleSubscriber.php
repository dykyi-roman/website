<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Symfony\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Translation\LocaleSwitcher;

final readonly class LocaleSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private LocaleSwitcher $localeSwitcher,
        private string $defaultLocale
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
        
        // Check URL parameter
        $locale = $request->query->get('lang', $request->getLocale());
        
        // Validate locale
        $supportedLocales = ['en', 'uk'];
        $locale = in_array($locale, $supportedLocales) ? $locale : $this->defaultLocale;

        // Set locale
        $this->localeSwitcher->setLocale($locale);
        $request->setLocale($locale);
    }
}
