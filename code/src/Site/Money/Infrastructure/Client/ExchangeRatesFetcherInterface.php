<?php

declare(strict_types=1);

namespace Site\Money\Infrastructure\Client;

use Site\Money\Domain\Exception\ExchangeRateApiException;

interface ExchangeRatesFetcherInterface
{
    /**
     * @throws ExchangeRateApiException
     */
    public function updateRates(): array;
}
