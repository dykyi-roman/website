<?php

declare(strict_types=1);

namespace Site\Location\Application\Query;

/**
 * @see GetCitiesDictionaryQueryHandler
 */
final readonly class GetCitiesDictionaryQuery
{
    public function __construct(
        public string $countryCode,
        public string $lang,
        public string $city,
    ) {
    }
}
