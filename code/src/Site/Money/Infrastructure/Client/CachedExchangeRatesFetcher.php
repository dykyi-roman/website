<?php

namespace Site\Money\Infrastructure\Client;

use Psr\SimpleCache\CacheInterface;
use Site\Money\Domain\Exception\ExchangeRateApiException;

final readonly class CachedExchangeRatesFetcher implements ExchangeRatesFetcherInterface
{
    private const string CACHE_KEY = 'exchange_rates';

    public function __construct(
        private ExchangeRatesFetcher $exchangeRatesFetcher,
        private CacheInterface $cache,
        private int $exchangeRateCacheTtl,
    ) {
    }

    /**
     * @throws ExchangeRateApiException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function updateRates(): array
    {
        $rates = $this->cache->get(self::CACHE_KEY);
        if (null !== $rates) {
            return $rates;
        }

        try {
            $rates = $this->exchangeRatesFetcher->updateRates();
            $this->cache->set(self::CACHE_KEY, $rates, $this->exchangeRateCacheTtl);

            return $rates;
        } catch (ExchangeRateApiException $exception) {
            $rates = $this->cache->get(self::CACHE_KEY);
            if (null !== $rates) {
                return $rates;
            }

            throw $exception;
        }
    }
}
