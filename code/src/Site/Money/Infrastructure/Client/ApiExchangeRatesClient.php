<?php

declare(strict_types=1);

namespace Site\Money\Infrastructure\Client;

use Site\Money\Domain\Services\ExchangeRatesClientInterface;
use Site\Money\Domain\ValueObject\Currency;
use Site\Money\Domain\ValueObject\Money;

final readonly class ApiExchangeRatesClient implements ExchangeRatesClientInterface
{
    public function __construct(
        private ExchangeRatesFetcherInterface $exchangeRatesFetcher,
        private string $exchangeRateBaseCurrency,
    ) {
    }

    /**
     * @throws \Site\Money\Domain\Exception\ExchangeRateApiException
     * @throws \InvalidArgumentException
     */
    public function convert(Money $money, Currency $toCurrency): Money
    {
        $rates = $this->exchangeRatesFetcher->updateRates();
        $this->validateCurrencies($rates, $money->getCurrency(), $toCurrency);

        if ($money->getCurrency()->equals($toCurrency)) {
            return $money;
        }

        // Convert to base currency first if not base currency
        $baseAmount = ($money->getCurrency()->equals(Currency::fromString($this->exchangeRateBaseCurrency)))
            ? $money->getAmount()
            : $money->getAmount() / $rates[$money->getCurrency()->getCode()];

        // Convert from base currency to target currency
        $convertedAmount = $baseAmount * $rates[$toCurrency->getCode()];

        return Money::create(round($convertedAmount, 4), $toCurrency);
    }

    public function getAvailableCurrencies(array $rates): array
    {
        return array_keys($rates);
    }

    private function validateCurrencies(array $rates, Currency ...$currencies): void
    {
        foreach ($currencies as $currency) {
            if (!isset($rates[$currency->getCode()])) {
                throw new \InvalidArgumentException(sprintf('Unsupported currency: %s. Available currencies: %s', $currency, implode(', ', $this->getAvailableCurrencies($rates))));
            }
        }
    }
}
