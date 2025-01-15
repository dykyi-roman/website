<?php

declare(strict_types=1);

namespace Site\Money\Tests\Unit\Presentation\Web\Twig;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Site\Money\Presentation\Web\Twig\CurrencyExtension;

#[CoversClass(CurrencyExtension::class)]
final class CurrencyExtensionTest extends TestCase
{
    public function testGetGlobalsReturnsExpectedStructure(): void
    {
        $defaultCurrency = 'USD';
        $supportedCurrencies = [
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
        ];

        $extension = new CurrencyExtension($defaultCurrency, $supportedCurrencies);
        $globals = $extension->getGlobals();

        $this->assertArrayHasKey('default_currency', $globals);
        $this->assertArrayHasKey('currencies', $globals);
        $this->assertSame($defaultCurrency, $globals['default_currency']);

        $expectedCurrencies = [
            ['code' => 'USD', 'symbol' => '$'],
            ['code' => 'EUR', 'symbol' => '€'],
            ['code' => 'GBP', 'symbol' => '£'],
        ];
        $this->assertEquals($expectedCurrencies, $globals['currencies']);
    }
}
