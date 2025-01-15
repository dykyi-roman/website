<?php

declare(strict_types=1);

namespace Site\Registration\Tests\Unit\DomainModel\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Site\Registration\DomainModel\Service\ReferralReceiver;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

#[CoversClass(ReferralReceiver::class)]
final class ReferralReceiverTest extends TestCase
{
    public function testReferralWhenRequestIsNull(): void
    {
        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->expects($this->once())
            ->method('getCurrentRequest')
            ->willReturn(null);

        $service = new ReferralReceiver($requestStack);
        $this->assertSame('', $service->referral());
    }

    public function testReferralFromCookies(): void
    {
        $request = $this->createMock(Request::class);
        $request->cookies = new InputBag(['reff' => 'cookie-value']);
        $request->query = new InputBag([]);

        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->expects($this->once())
            ->method('getCurrentRequest')
            ->willReturn($request);

        $service = new ReferralReceiver($requestStack);
        $this->assertSame('cookie-value', $service->referral());
    }

    public function testReferralFromQueryParameters(): void
    {
        $request = $this->createMock(Request::class);
        $request->cookies = new InputBag([]);
        $request->query = new InputBag(['reff' => 'query-value']);

        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->expects($this->once())
            ->method('getCurrentRequest')
            ->willReturn($request);

        $service = new ReferralReceiver($requestStack);
        $this->assertSame('query-value', $service->referral());
    }

    public function testReferralWhenNotFound(): void
    {
        $request = $this->createMock(Request::class);
        $request->cookies = new InputBag([]);
        $request->query = new InputBag([]);

        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->expects($this->once())
            ->method('getCurrentRequest')
            ->willReturn($request);

        $service = new ReferralReceiver($requestStack);
        $this->assertSame('', $service->referral());
    }

    public function testReferralWithCustomName(): void
    {
        $request = $this->createMock(Request::class);
        $request->cookies = new InputBag(['custom' => 'cookie-value']);
        $request->query = new InputBag([]);

        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->expects($this->once())
            ->method('getCurrentRequest')
            ->willReturn($request);

        $service = new ReferralReceiver($requestStack);
        $this->assertSame('cookie-value', $service->referral('custom'));
    }
}
