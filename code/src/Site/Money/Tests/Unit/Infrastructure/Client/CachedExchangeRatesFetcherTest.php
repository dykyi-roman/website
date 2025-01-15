<?php

declare(strict_types=1);

namespace Site\Money\Tests\Unit\Infrastructure\Client;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;
use Site\Money\DomainModel\Exception\ExchangeRateApiException;
use Site\Money\Infrastructure\Client\CachedExchangeRatesFetcher;
use Site\Money\Infrastructure\Client\ExchangeRatesFetcherInterface;

#[CoversClass(CachedExchangeRatesFetcher::class)]
final class CachedExchangeRatesFetcherTest extends TestCase
{
    private ExchangeRatesFetcherInterface&MockObject $exchangeRatesFetcher;
    private CacheInterface&MockObject $cache;
    private CachedExchangeRatesFetcher $cachedFetcher;

    private const int CACHE_TTL = 3600;

    protected function setUp(): void
    {
        $this->exchangeRatesFetcher = $this->createMock(ExchangeRatesFetcherInterface::class);
        $this->cache = $this->createMock(CacheInterface::class);
        $this->cachedFetcher = new CachedExchangeRatesFetcher(
            $this->exchangeRatesFetcher,
            $this->cache,
            self::CACHE_TTL
        );
    }

    public function testUpdateRatesReturnsCachedRatesWhenAvailable(): void
    {
        $cachedRates = ['USD' => 1.0, 'EUR' => 0.85];

        $this->cache->expects(self::once())
            ->method('get')
            ->with('exchange_rates')
            ->willReturn($cachedRates);

        $this->exchangeRatesFetcher->expects(self::never())
            ->method('updateRates');

        $result = $this->cachedFetcher->updateRates();
        self::assertEquals($cachedRates, $result);
    }

    public function testUpdateRatesFetchesNewRatesWhenCacheEmpty(): void
    {
        $rates = ['USD' => 1.0, 'EUR' => 0.85];

        $this->cache->expects(self::once())
            ->method('get')
            ->with('exchange_rates')
            ->willReturn(null);

        $this->exchangeRatesFetcher->expects(self::once())
            ->method('updateRates')
            ->willReturn($rates);

        $this->cache->expects(self::once())
            ->method('set')
            ->with('exchange_rates', $rates, self::CACHE_TTL);

        $result = $this->cachedFetcher->updateRates();
        self::assertEquals($rates, $result);
    }

    public function testUpdateRatesUsesCachedRatesAsFallbackWhenApiFails(): void
    {
        $cachedRates = ['USD' => 1.0, 'EUR' => 0.85];

        $this->cache->expects(self::exactly(2))
            ->method('get')
            ->with('exchange_rates')
            ->willReturnOnConsecutiveCalls(null, $cachedRates);

        $this->exchangeRatesFetcher->expects(self::once())
            ->method('updateRates')
            ->willThrowException(new ExchangeRateApiException('API Error'));

        $result = $this->cachedFetcher->updateRates();
        self::assertEquals($cachedRates, $result);
    }

    public function testUpdateRatesThrowsExceptionWhenApiFailsAndNoCachedRates(): void
    {
        $this->cache->expects(self::exactly(2))
            ->method('get')
            ->with('exchange_rates')
            ->willReturn(null);

        $this->exchangeRatesFetcher->expects(self::once())
            ->method('updateRates')
            ->willThrowException(new ExchangeRateApiException('API Error'));

        $this->expectException(ExchangeRateApiException::class);
        $this->expectExceptionMessage('API Error');

        $this->cachedFetcher->updateRates();
    }

    public function testUpdateRatesThrowsExceptionForInvalidCachedRatesFormat(): void
    {
        $this->cache->expects(self::once())
            ->method('get')
            ->with('exchange_rates')
            ->willReturn('invalid');

        $this->expectException(ExchangeRateApiException::class);
        $this->expectExceptionMessage('Invalid cached exchange rates format');

        $this->cachedFetcher->updateRates();
    }

    public function testUpdateRatesThrowsExceptionForInvalidCachedRateValue(): void
    {
        $this->cache->expects(self::once())
            ->method('get')
            ->with('exchange_rates')
            ->willReturn(['USD' => 'invalid']);

        $this->expectException(ExchangeRateApiException::class);
        $this->expectExceptionMessage('Invalid cached rate format');

        $this->cachedFetcher->updateRates();
    }
}
