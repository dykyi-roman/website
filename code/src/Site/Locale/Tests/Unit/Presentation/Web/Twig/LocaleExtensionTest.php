<?php

declare(strict_types=1);

namespace Site\Locale\Tests\Unit\Presentation\Web\Twig;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Site\Locale\Presentation\Web\Twig\LocaleExtension;

#[CoversClass(LocaleExtension::class)]
final class LocaleExtensionTest extends TestCase
{
    public function testGetGlobalsReturnsFormattedLocales(): void
    {
        // Given
        $supportedLocales = ['en' => 'en', 'es' => 'es', 'uk' => 'uk'];
        $extension = new LocaleExtension($supportedLocales);

        // When
        $globals = $extension->getGlobals();

        // Then
        /* @phpstan-ignore-next-line */
        $this->assertIsArray($globals);
        $this->assertArrayHasKey('locales', $globals);
        $this->assertIsArray($globals['locales']);
        $this->assertCount(3, $globals['locales']);
        $this->assertEquals(
            [
                ['code' => 'en'],
                ['code' => 'es'],
                ['code' => 'uk'],
            ],
            $globals['locales']
        );
    }

    public function testGetGlobalsReturnsEmptyArrayWhenNoLocalesProvided(): void
    {
        // Given
        $extension = new LocaleExtension([]);

        // When
        $globals = $extension->getGlobals();

        // Then
        /* @phpstan-ignore-next-line */
        $this->assertIsArray($globals);
        $this->assertArrayHasKey('locales', $globals);
        $this->assertEmpty($globals['locales']);
    }
}
