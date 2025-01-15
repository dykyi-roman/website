<?php

declare(strict_types=1);

namespace Site\Locale\Tests\Unit\DomainModel\Service\LocaleResolver;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Site\Locale\DomainModel\Service\LocaleResolver\AcceptLanguageLocaleResolver;
use Symfony\Component\HttpFoundation\Request;

#[CoversClass(AcceptLanguageLocaleResolver::class)]
final class AcceptLanguageLocaleResolverTest extends TestCase
{
    private AcceptLanguageLocaleResolver $resolver;

    protected function setUp(): void
    {
        $this->resolver = new AcceptLanguageLocaleResolver();
    }

    public function testReturnsFirstLocaleFromAcceptLanguageHeader(): void
    {
        $request = Request::create('/');
        $request->headers->set('Accept-Language', 'en-US,en;q=0.9,uk;q=0.8');

        $this->assertSame('en', $this->resolver->resolve($request));
    }

    public function testHandlesLocaleWithQualityValue(): void
    {
        $request = Request::create('/');
        $request->headers->set('Accept-Language', 'fr-FR;q=0.9');

        $this->assertSame('fr', $this->resolver->resolve($request));
    }

    public function testReturnsLowercaseLocale(): void
    {
        $request = Request::create('/');
        $request->headers->set('Accept-Language', 'EN-US');

        $this->assertSame('en', $this->resolver->resolve($request));
    }

    public function testReturnsNullForEmptyLocale(): void
    {
        $request = Request::create('/');
        $request->headers->set('Accept-Language', '');

        $this->assertNull($this->resolver->resolve($request));
    }

    public function testHandlesMultipleLocalesWithSpaces(): void
    {
        $request = Request::create('/');
        $request->headers->set('Accept-Language', 'es-ES, fr;q=0.9, en;q=0.8');

        $this->assertSame('es', $this->resolver->resolve($request));
    }
}
