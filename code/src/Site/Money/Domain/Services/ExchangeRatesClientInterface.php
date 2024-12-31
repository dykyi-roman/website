<?php

declare(strict_types=1);

namespace Site\Money\Domain\Services;

use Site\Money\Domain\ValueObject\Currency;
use Site\Money\Domain\ValueObject\Money;

interface ExchangeRatesClientInterface
{
    /**
     * @throws \Site\Money\Domain\Exception\ExchangeRateApiException
     * @throws \InvalidArgumentException
     */
    public function convert(Money $money, Currency $toCurrency): Money;
}
