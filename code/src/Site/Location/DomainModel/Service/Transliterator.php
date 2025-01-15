<?php

declare(strict_types=1);

namespace Site\Location\DomainModel\Service;

final class Transliterator
{
    /** @var array<string, string> */
    private static array $map = [
        // Cyrillic (Russian default with Ukrainian support)
        'а' => 'a',    'б' => 'b',    'в' => 'v',    'г' => 'g',    'д' => 'd',
        'е' => 'e',    'ж' => 'zh',   'з' => 'z',    'й' => 'y',    'к' => 'k',
        'л' => 'l',    'м' => 'm',    'н' => 'n',    'о' => 'o',    'п' => 'p',
        'р' => 'r',    'с' => 's',    'т' => 't',    'у' => 'u',    'ф' => 'f',
        'х' => 'h',    'ц' => 'ts',   'ч' => 'ch',   'ш' => 'sh',   'щ' => 'sch',
        'ь' => '',     'ю' => 'yu',   'я' => 'ya',   'и' => 'i',    'ё' => 'e',
        'ъ' => '',     'ы' => 'y',    'э' => 'e',

        // Ukrainian specific (unique characters)
        'є' => 'ye',   'і' => 'i',    'ї' => 'i',   'ґ' => 'g',

        // Common European Characters (shared between languages)
        'á' => 'a',    'à' => 'a',    'ã' => 'a',    'â' => 'a',    'ä' => 'a',
        'å' => 'a',    'ā' => 'a',    'ą' => 'a',    'ă' => 'a',    'ç' => 'c',
        'é' => 'e',    'è' => 'e',    'ê' => 'e',    'ë' => 'e',    'ė' => 'e',
        'ē' => 'e',    'ę' => 'e',    'ě' => 'e',
        'í' => 'i',    'ì' => 'i',    'î' => 'i',    'ï' => 'i',    'ī' => 'i',
        'į' => 'i',
        'ó' => 'o',    'ò' => 'o',    'õ' => 'o',    'ô' => 'o',    'ö' => 'o',
        'ő' => 'o',    'ø' => 'o',
        'ú' => 'u',    'ù' => 'u',    'ū' => 'u',    'û' => 'u',    'ü' => 'u',
        'ű' => 'u',    'ų' => 'u',
        'ý' => 'y',    'ÿ' => 'y',

        // Germanic Languages Specific (German, Dutch)
        'ß' => 'ss',   'ĳ' => 'ij',   'œ' => 'oe',   'æ' => 'ae',

        // Slavic Languages Specific (Czech, Slovak, Croatian, Serbian, Polish)
        'č' => 'c',    'ć' => 'c',    'ď' => 'd',    'ђ' => 'dj',   'đ' => 'dj',
        'ľ' => 'l',    'ł' => 'l',    'ĺ' => 'l',    'ļ' => 'l',    'њ' => 'nj',
        'ň' => 'n',    'ń' => 'n',    'ņ' => 'n',    'ŕ' => 'r',    'ř' => 'r',
        'š' => 's',    'ś' => 's',    'ș' => 's',    'ť' => 't',    'ț' => 't',
        'ž' => 'z',    'ź' => 'z',    'ż' => 'z',
        'dž' => 'dz',  'lj' => 'lj',  'nj' => 'nj',

        // Baltic Languages Specific (Latvian, Lithuanian)
        'ģ' => 'g',    'ķ' => 'k',

        // Maltese Specific
        'ċ' => 'c',    'ġ' => 'g',    'għ' => 'gh',  'ħ' => 'h',

        // Greek
        'α' => 'a',    'β' => 'v',    'γ' => 'g',    'δ' => 'd',    'ε' => 'e',
        'ζ' => 'z',    'η' => 'i',    'θ' => 'th',   'ι' => 'i',    'κ' => 'k',
        'λ' => 'l',    'μ' => 'm',    'ν' => 'n',    'ξ' => 'x',    'ο' => 'o',
        'π' => 'p',    'ρ' => 'r',    'σ' => 's',    'τ' => 't',    'υ' => 'y',
        'φ' => 'f',    'χ' => 'ch',   'ψ' => 'ps',   'ω' => 'o',    'ή' => 'i',
    ];

    /**
     * Transliterates a string from various languages to Latin characters.
     */
    public static function transliterate(string $text): string
    {
        // Detect if text contains Ukrainian-specific characters
        $isUkrainian = str_contains($text, 'і') || str_contains($text, 'І')
                      || str_contains($text, 'ї') || str_contains($text, 'Ї')
                      || str_contains($text, 'є') || str_contains($text, 'Є')
                      || str_contains($text, 'ґ') || str_contains($text, 'Ґ');

        // Create map with uppercase versions
        $map = self::$map;
        foreach (self::$map as $from => $to) {
            $map[mb_strtoupper($from, 'UTF-8')] = ucfirst($to);
        }

        // For Ukrainian text, override 'и' transliteration
        if ($isUkrainian) {
            $map['и'] = 'y';
            $map['И'] = 'Y';
        }

        // Replace characters according to the map
        $result = str_replace(
            array_keys($map),
            array_values($map),
            mb_strtolower($text, 'UTF-8'),
        );

        // Remove any remaining non-ASCII characters
        return (string) preg_replace('/[^\x20-\x7E]/u', '', $result);
    }
}
