<?php

declare(strict_types=1);

namespace Site\Money\Tests\Unit\Infrastructure\Client;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shared\DomainModel\ValueObject\Currency;
use Shared\DomainModel\ValueObject\Money;
use Site\Money\Infrastructure\Client\ApiExchangeRatesClient;
use Site\Money\Infrastructure\Client\ExchangeRatesFetcherInterface;

#[CoversClass(ApiExchangeRatesClient::class)]
final class ApiExchangeRatesClientTest extends TestCase
{
    private ExchangeRatesFetcherInterface&MockObject $exchangeRatesFetcher;
    private ApiExchangeRatesClient $client;

    private const string BASE_CURRENCY = 'USD';

    protected function setUp(): void
    {
        $this->exchangeRatesFetcher = $this->createMock(ExchangeRatesFetcherInterface::class);
        $this->client = new ApiExchangeRatesClient($this->exchangeRatesFetcher, self::BASE_CURRENCY);
    }

    public function testConvertSameCurrencyReturnsOriginalMoney(): void
    {
        $money = Money::create(100, Currency::fromString('USD'));

        $this->exchangeRatesFetcher->expects(self::once())
            ->method('updateRates')
            ->willReturn(['USD' => 1.0]);

        $result = $this->client->convert($money, Currency::fromString('USD'));

        self::assertTrue($money->equals($result));
    }

    public function testConvertDifferentCurrencyCalculatesCorrectly(): void
    {
        $money = Money::create(100, Currency::fromString('USD'));
        $rates = [
            'USD' => 1.0,
            'EUR' => 0.85,
        ];

        $this->exchangeRatesFetcher->expects(self::once())
            ->method('updateRates')
            ->willReturn($rates);

        $result = $this->client->convert($money, Currency::fromString('EUR'));

        self::assertEquals(85.0, $result->getAmount());
        self::assertEquals('EUR', $result->getCurrency()->code());
    }

    public function testGetAvailableCurrencies(): void
    {
        $rates = [
            'USD' => 1.0,
            'EUR' => 0.85,
            'GBP' => 0.73,
        ];

        $currencies = $this->client->getAvailableCurrencies($rates);

        self::assertEquals(['USD', 'EUR', 'GBP'], $currencies);
    }

    public function testConvertThrowsExceptionForUnsupportedCurrency(): void
    {
        $money = Money::create(100, Currency::fromString('USD'));
        $rates = ['USD' => 1.0];

        $this->exchangeRatesFetcher->expects(self::once())
            ->method('updateRates')
            ->willReturn($rates);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported currency: EUR');

        $this->client->convert($money, Currency::fromString('EUR'));
    }
}
