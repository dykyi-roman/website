<?php

declare(strict_types=1);

namespace Site\Locale\Tests\Unit\DomainModel\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Site\Locale\DomainModel\Service\ChainLocaleResolver;
use Site\Locale\DomainModel\Service\LocaleResolverInterface;
use Symfony\Component\HttpFoundation\Request;

#[CoversClass(ChainLocaleResolver::class)]
final class ChainLocaleResolverTest extends TestCase
{
    public function testReturnsNullWhenNoResolversProvided(): void
    {
        $resolver = new ChainLocaleResolver([]);
        $request = Request::createFromGlobals();

        $this->assertNull($resolver->resolve($request));
    }

    public function testReturnsNullWhenNoResolverFindsLocale(): void
    {
        $resolver1 = $this->createMock(LocaleResolverInterface::class);
        $resolver1->method('resolve')->willReturn(null);

        $resolver2 = $this->createMock(LocaleResolverInterface::class);
        $resolver2->method('resolve')->willReturn(null);

        $resolver = new ChainLocaleResolver([$resolver1, $resolver2]);
        $request = Request::createFromGlobals();

        $this->assertNull($resolver->resolve($request));
    }

    public function testReturnsFirstNonNullLocale(): void
    {
        $resolver1 = $this->createMock(LocaleResolverInterface::class);
        $resolver1->method('resolve')->willReturn('en');

        $resolver2 = $this->createMock(LocaleResolverInterface::class);
        $resolver2->expects($this->never())->method('resolve');

        $resolver = new ChainLocaleResolver([$resolver1, $resolver2]);
        $request = Request::createFromGlobals();

        $this->assertSame('en', $resolver->resolve($request));
    }

    public function testTriesNextResolverWhenFirstReturnsNull(): void
    {
        $resolver1 = $this->createMock(LocaleResolverInterface::class);
        $resolver1->method('resolve')->willReturn(null);

        $resolver2 = $this->createMock(LocaleResolverInterface::class);
        $resolver2->method('resolve')->willReturn('fr');

        $resolver = new ChainLocaleResolver([$resolver1, $resolver2]);
        $request = Request::createFromGlobals();

        $this->assertSame('fr', $resolver->resolve($request));
    }
}
