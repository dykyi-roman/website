<?php

namespace Site\Money\Infrastructure\Client;

use Psr\SimpleCache\CacheInterface;
use Site\Money\DomainModel\Exception\ExchangeRateApiException;

final readonly class CachedExchangeRatesFetcher implements ExchangeRatesFetcherInterface
{
    private const string CACHE_KEY = 'exchange_rates';

    public function __construct(
        private ExchangeRatesFetcherInterface $exchangeRatesFetcher,
        private CacheInterface $cache,
        private int $exchangeRateCacheTtl,
    ) {
    }

    /**
     * @return array<string, float>
     *
     * @throws ExchangeRateApiException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function updateRates(): array
    {
        $cachedRates = $this->cache->get(self::CACHE_KEY);
        if (null !== $cachedRates) {
            return $this->validateAndNormalizeCachedRates($cachedRates);
        }

        try {
            $rates = $this->exchangeRatesFetcher->updateRates();
            $this->cache->set(self::CACHE_KEY, $rates, $this->exchangeRateCacheTtl);

            return $rates;
        } catch (ExchangeRateApiException $exception) {
            // Try to use cached rates as fallback
            $cachedRates = $this->cache->get(self::CACHE_KEY);
            if (null !== $cachedRates) {
                return $this->validateAndNormalizeCachedRates($cachedRates);
            }

            throw $exception;
        }
    }

    /**
     * @return array<string, float>
     *
     * @throws ExchangeRateApiException
     */
    private function validateAndNormalizeCachedRates(mixed $cachedRates): array
    {
        if (!is_array($cachedRates)) {
            throw new ExchangeRateApiException('Invalid cached exchange rates format');
        }

        $rates = [];
        foreach ($cachedRates as $currency => $rate) {
            if (!is_string($currency) || !is_numeric($rate)) {
                throw new ExchangeRateApiException('Invalid cached rate format');
            }
            $rates[$currency] = (float) $rate;
        }

        return $rates;
    }
}
