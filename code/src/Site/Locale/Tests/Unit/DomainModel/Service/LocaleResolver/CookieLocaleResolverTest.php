<?php

declare(strict_types=1);

namespace Site\Locale\Tests\Unit\DomainModel\Service\LocaleResolver;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Site\Locale\DomainModel\Service\LocaleResolver\CookieLocaleResolver;
use Symfony\Component\HttpFoundation\Request;

#[CoversClass(CookieLocaleResolver::class)]
final class CookieLocaleResolverTest extends TestCase
{
    private CookieLocaleResolver $resolver;

    protected function setUp(): void
    {
        $this->resolver = new CookieLocaleResolver();
    }

    public function testReturnsNullWhenNoCookiePresent(): void
    {
        $request = Request::create('/');

        $this->assertNull($this->resolver->resolve($request));
    }

    public function testReturnsLocaleFromCookie(): void
    {
        $request = Request::create('/');
        $request->cookies->set('locale', 'fr');

        $this->assertSame('fr', $this->resolver->resolve($request));
    }

    public function testReturnsNullWhenCookieIsEmpty(): void
    {
        $request = Request::create('/');
        $request->cookies->set('locale', '');

        $this->assertEmpty($this->resolver->resolve($request));
    }
}
