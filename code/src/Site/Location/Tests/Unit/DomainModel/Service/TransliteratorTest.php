<?php

declare(strict_types=1);

namespace Site\Location\Tests\Unit\DomainModel\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Site\Location\DomainModel\Service\Transliterator;

#[CoversClass(Transliterator::class)]
final class TransliteratorTest extends TestCase
{
    /**
     * @return array<string, array{string, string}>
     */
    public static function transliterationDataProvider(): array
    {
        return [
            'cyrillic' => ['Москва', 'moskva'],
            'ukrainian' => ['Київ', 'kyiv'],
            'german' => ['München', 'munchen'],
            'french' => ['François', 'francois'],
            'polish' => ['Łódź', 'lodz'],
            'greek' => ['Αθήνα', 'athina'],
            'mixed case' => ['ПаРиЖ', 'parizh'],
            'multiple characters' => ['щука', 'schuka'],
            'with spaces' => ['Нью Йорк', 'nyu york'],
            'mixed languages' => ['Café München', 'cafe munchen'],
            'special characters' => ['Stadt-Straße', 'stadt-strasse'],
            'non-ascii removal' => ['city™®', 'city'],
        ];
    }

    #[DataProvider('transliterationDataProvider')]
    public function testTransliterate(string $input, string $expected): void
    {
        $result = Transliterator::transliterate($input);

        $this->assertSame($expected, $result);
    }
}
