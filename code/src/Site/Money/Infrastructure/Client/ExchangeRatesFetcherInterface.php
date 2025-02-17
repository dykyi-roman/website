<?php

declare(strict_types=1);

namespace Site\Money\Infrastructure\Client;

use Site\Money\DomainModel\Exception\ExchangeRateApiException;

interface ExchangeRatesFetcherInterface
{
    /**
     * @return array<string, float>
     *
     * @throws ExchangeRateApiException
     */
    public function updateRates(): array;
}
