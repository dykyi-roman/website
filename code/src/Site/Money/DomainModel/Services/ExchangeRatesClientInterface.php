<?php

declare(strict_types=1);

namespace Site\Money\DomainModel\Services;

use Shared\DomainModel\ValueObject\Currency;
use Shared\DomainModel\ValueObject\Money;

interface ExchangeRatesClientInterface
{
    /**
     * @throws \Site\Money\DomainModel\Exception\ExchangeRateApiException
     * @throws \InvalidArgumentException
     */
    public function convert(Money $money, Currency $toCurrency): Money;
}
