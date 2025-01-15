<?php

declare(strict_types=1);

namespace Site\Locale\Tests\Unit\Infrastructure\Symfony\EventSubscriber;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Site\Locale\DomainModel\Service\LocaleResolverInterface;
use Site\Locale\Infrastructure\Symfony\EventSubscriber\LocaleSubscriber;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Translation\LocaleSwitcher;

#[CoversClass(LocaleSubscriber::class)]
final class LocaleSubscriberTest extends TestCase
{
    private const string DEFAULT_LOCALE = 'en';
    private const array SUPPORTED_LOCALES = ['en', 'es', 'uk'];

    private LocaleSubscriber $subscriber;
    private MockObject&LocaleSwitcher $localeSwitcher;
    private MockObject&LocaleResolverInterface $localeResolver;

    protected function setUp(): void
    {
        $this->localeSwitcher = $this->createMock(LocaleSwitcher::class);
        $this->localeResolver = $this->createMock(LocaleResolverInterface::class);

        $this->subscriber = new LocaleSubscriber(
            self::DEFAULT_LOCALE,
            self::SUPPORTED_LOCALES,
            $this->localeSwitcher,
            $this->localeResolver
        );
    }

    public function testGetSubscribedEvents(): void
    {
        $events = LocaleSubscriber::getSubscribedEvents();

        self::assertArrayHasKey(KernelEvents::REQUEST, $events);
        self::assertSame([['onKernelRequest', 20]], $events[KernelEvents::REQUEST]);
    }

    public function testOnKernelRequestWithSupportedLocale(): void
    {
        $request = new Request();
        $event = $this->createRequestEvent($request);
        $resolvedLocale = 'es';

        $this->localeResolver
            ->expects(self::once())
            ->method('resolve')
            ->with($request)
            ->willReturn($resolvedLocale);

        $this->localeSwitcher
            ->expects(self::once())
            ->method('setLocale')
            ->with($resolvedLocale);

        $this->subscriber->onKernelRequest($event);

        self::assertSame($resolvedLocale, $request->getLocale());
        self::assertSame($resolvedLocale, $request->cookies->get('locale'));
    }

    public function testOnKernelRequestWithUnsupportedLocale(): void
    {
        $request = new Request();
        $event = $this->createRequestEvent($request);
        $unsupportedLocale = 'fr';

        $this->localeResolver
            ->expects(self::once())
            ->method('resolve')
            ->with($request)
            ->willReturn($unsupportedLocale);

        $this->localeSwitcher
            ->expects(self::once())
            ->method('setLocale')
            ->with(self::DEFAULT_LOCALE);

        $this->subscriber->onKernelRequest($event);

        self::assertSame(self::DEFAULT_LOCALE, $request->getLocale());
        self::assertSame(self::DEFAULT_LOCALE, $request->cookies->get('locale'));
    }

    public function testOnKernelRequestWithNullLocale(): void
    {
        $request = new Request();
        $event = $this->createRequestEvent($request);

        $this->localeResolver
            ->expects(self::once())
            ->method('resolve')
            ->with($request)
            ->willReturn(null);

        $this->localeSwitcher
            ->expects(self::once())
            ->method('setLocale')
            ->with(self::DEFAULT_LOCALE);

        $this->subscriber->onKernelRequest($event);

        self::assertSame(self::DEFAULT_LOCALE, $request->getLocale());
        self::assertSame(self::DEFAULT_LOCALE, $request->cookies->get('locale'));
    }

    private function createRequestEvent(Request $request): RequestEvent
    {
        /** @var MockObject&\Symfony\Component\HttpKernel\HttpKernelInterface $kernel */
        $kernel = $this->createMock(\Symfony\Component\HttpKernel\HttpKernelInterface::class);

        return new RequestEvent(
            $kernel,
            $request,
            \Symfony\Component\HttpKernel\HttpKernelInterface::MAIN_REQUEST
        );
    }
}
