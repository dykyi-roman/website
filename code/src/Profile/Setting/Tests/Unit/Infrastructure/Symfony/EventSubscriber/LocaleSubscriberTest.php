<?php

declare(strict_types=1);

namespace Profile\Setting\Tests\Unit\Infrastructure\Symfony\EventSubscriber;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Profile\Setting\DomainModel\Enum\PropertyCategory;
use Profile\Setting\DomainModel\Enum\PropertyName;
use Profile\Setting\DomainModel\Model\Setting;
use Profile\Setting\DomainModel\Repository\SettingRepositoryInterface;
use Profile\Setting\DomainModel\ValueObject\Property;
use Profile\Setting\Infrastructure\Symfony\EventSubscriber\LocaleSubscriber;
use Profile\User\DomainModel\Model\User;
use Shared\DomainModel\ValueObject\UserId;
use Shared\Infrastructure\Security\Symfony\UserFetcher;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Translation\LocaleSwitcher;

#[CoversClass(LocaleSubscriber::class)]
final class LocaleSubscriberTest extends TestCase
{
    private LocaleSwitcher&MockObject $localeSwitcher;
    private SettingRepositoryInterface&MockObject $settingRepository;
    private Security&MockObject $security;
    private LocaleSubscriber $subscriber;

    protected function setUp(): void
    {
        $this->localeSwitcher = $this->createMock(LocaleSwitcher::class);
        $this->settingRepository = $this->createMock(SettingRepositoryInterface::class);
        $this->security = $this->createMock(Security::class);
        $this->subscriber = new LocaleSubscriber(
            $this->localeSwitcher,
            $this->settingRepository,
            new UserFetcher($this->security),
        );
    }

    public function testGetSubscribedEvents(): void
    {
        $events = LocaleSubscriber::getSubscribedEvents();

        self::assertArrayHasKey(KernelEvents::REQUEST, $events);
        self::assertSame([['onKernelRequest', -8]], $events[KernelEvents::REQUEST]);
    }

    public function testOnKernelRequestWithLangQueryParam(): void
    {
        $request = new Request(['lang' => 'en']);
        $event = $this->createRequestEvent($request);

        $this->security->expects(self::never())->method('getToken');
        $this->settingRepository->expects(self::never())->method('findByName');
        $this->localeSwitcher->expects(self::never())->method('setLocale');

        $this->subscriber->onKernelRequest($event);
    }

    public function testOnKernelRequestWithAuthenticationException(): void
    {
        $request = new Request();
        $event = $this->createRequestEvent($request);

        $this->security
            ->expects(self::once())
            ->method('getToken')
            ->willReturn(null);

        $this->settingRepository->expects(self::never())->method('findByName');
        $this->localeSwitcher->expects(self::never())->method('setLocale');

        $this->subscriber->onKernelRequest($event);
    }

    public function testOnKernelRequestWithNoSetting(): void
    {
        $userId = new UserId('550e8400-e29b-41d4-a716-446655440000');
        $user = $this->createMock(User::class);
        $user->method('id')->willReturn($userId);

        $request = new Request();
        $event = $this->createRequestEvent($request);

        $token = $this->createMock(\Symfony\Component\Security\Core\Authentication\Token\TokenInterface::class);
        $token->method('getUser')->willReturn($user);

        $this->security
            ->expects(self::once())
            ->method('getToken')
            ->willReturn($token);

        $this->settingRepository
            ->expects(self::once())
            ->method('findByName')
            ->with($userId, PropertyName::SETTINGS_GENERAL_LANGUAGE)
            ->willReturn(null);

        $this->localeSwitcher->expects(self::never())->method('setLocale');

        $this->subscriber->onKernelRequest($event);
    }

    public function testOnKernelRequestWithSetting(): void
    {
        $userId = new UserId('550e8400-e29b-41d4-a716-446655440000');
        $locale = 'fr';

        $user = $this->createMock(User::class);
        $user->method('id')->willReturn($userId);

        $property = new Property(
            PropertyCategory::GENERAL,
            PropertyName::SETTINGS_GENERAL_LANGUAGE,
            $locale
        );

        $setting = $this->createMock(Setting::class);
        $setting->method('getProperty')->willReturn($property);

        $request = new Request();
        $event = $this->createRequestEvent($request);

        $token = $this->createMock(\Symfony\Component\Security\Core\Authentication\Token\TokenInterface::class);
        $token->method('getUser')->willReturn($user);

        $this->security
            ->expects(self::once())
            ->method('getToken')
            ->willReturn($token);

        $this->settingRepository
            ->expects(self::once())
            ->method('findByName')
            ->with($userId, PropertyName::SETTINGS_GENERAL_LANGUAGE)
            ->willReturn($setting);

        $this->localeSwitcher
            ->expects(self::once())
            ->method('setLocale')
            ->with($locale);

        $this->subscriber->onKernelRequest($event);

        self::assertSame($locale, $request->getLocale());
        self::assertSame($locale, $request->cookies->get('locale'));
    }

    private function createRequestEvent(Request $request): RequestEvent
    {
        /** @var HttpKernelInterface&MockObject $kernel */
        $kernel = $this->createMock(HttpKernelInterface::class);

        return new RequestEvent(
            $kernel,
            $request,
            HttpKernelInterface::MAIN_REQUEST
        );
    }
}
