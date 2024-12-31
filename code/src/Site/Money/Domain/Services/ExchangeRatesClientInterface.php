<?php

declare(strict_types=1);

namespace Site\Money\Domain\Services;

use Shared\DomainModel\ValueObject\Currency;
use Shared\DomainModel\ValueObject\Money;

interface ExchangeRatesClientInterface
{
    /**
     * @throws \Site\Money\Domain\Exception\ExchangeRateApiException
     * @throws \InvalidArgumentException
     */
    public function convert(Money $money, Currency $toCurrency): Money;
}
