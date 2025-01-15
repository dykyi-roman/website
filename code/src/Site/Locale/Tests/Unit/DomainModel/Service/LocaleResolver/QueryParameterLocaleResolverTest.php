<?php

declare(strict_types=1);

namespace Site\Locale\Tests\Unit\DomainModel\Service\LocaleResolver;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Site\Locale\DomainModel\Service\LocaleResolver\QueryParameterLocaleResolver;
use Symfony\Component\HttpFoundation\Request;

#[CoversClass(QueryParameterLocaleResolver::class)]
final class QueryParameterLocaleResolverTest extends TestCase
{
    private QueryParameterLocaleResolver $resolver;

    protected function setUp(): void
    {
        $this->resolver = new QueryParameterLocaleResolver();
    }

    public function testReturnsNullWhenNoLangParameter(): void
    {
        $request = Request::create('/');

        $this->assertNull($this->resolver->resolve($request));
    }

    public function testReturnsLocaleFromLangParameter(): void
    {
        $request = Request::create('/?lang=fr');

        $this->assertSame('fr', $this->resolver->resolve($request));
    }

    public function testReturnsNullWhenLangParameterIsEmpty(): void
    {
        $request = Request::create('/?lang=');

        $this->assertEmpty($this->resolver->resolve($request));
    }

    public function testHandlesMultipleQueryParameters(): void
    {
        $request = Request::create('/?page=1&lang=de&sort=desc');

        $this->assertSame('de', $this->resolver->resolve($request));
    }
}
