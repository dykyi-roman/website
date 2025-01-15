<?php

declare(strict_types=1);

namespace Site\Money\Tests\Unit\Infrastructure\Client;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Site\Money\DomainModel\Exception\ExchangeRateApiException;
use Site\Money\Infrastructure\Client\ExchangeRatesFetcher;

#[CoversClass(ExchangeRatesFetcher::class)]
final class ExchangeRatesFetcherTest extends TestCase
{
    private ClientInterface&MockObject $httpClient;
    private RequestFactoryInterface&MockObject $requestFactory;
    private RequestInterface&MockObject $request;
    private ResponseInterface&MockObject $response;
    private StreamInterface&MockObject $stream;
    private ExchangeRatesFetcher $fetcher;

    private const string API_HOST = 'https://api.example.com/';
    private const string API_KEY = 'test-key';
    private const string BASE_CURRENCY = 'USD';

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(ClientInterface::class);
        $this->requestFactory = $this->createMock(RequestFactoryInterface::class);
        $this->request = $this->createMock(RequestInterface::class);
        $this->response = $this->createMock(ResponseInterface::class);
        $this->stream = $this->createMock(StreamInterface::class);

        $this->fetcher = new ExchangeRatesFetcher(
            $this->httpClient,
            $this->requestFactory,
            self::API_HOST,
            self::API_KEY,
            self::BASE_CURRENCY
        );
    }

    public function testUpdateRatesSuccessfully(): void
    {
        $expectedRates = [
            'USD' => 1.0,
            'EUR' => 0.85,
            'GBP' => 0.73,
        ];

        $responseData = [
            'result' => 'success',
            'conversion_rates' => $expectedRates,
        ];

        $this->setupHttpMocks($responseData);

        $rates = $this->fetcher->updateRates();
        self::assertEquals($expectedRates, $rates);
    }

    public function testUpdateRatesThrowsExceptionOnNonSuccessStatusCode(): void
    {
        $this->requestFactory->expects(self::once())
            ->method('createRequest')
            ->with('GET', self::API_HOST.self::API_KEY.'/latest/'.self::BASE_CURRENCY)
            ->willReturn($this->request);

        $this->httpClient->expects(self::once())
            ->method('sendRequest')
            ->with($this->request)
            ->willReturn($this->response);

        $this->response->expects(self::once())
            ->method('getStatusCode')
            ->willReturn(404);

        $this->expectException(ExchangeRateApiException::class);
        $this->expectExceptionMessage('API request failed with status code: 404');

        $this->fetcher->updateRates();
    }

    public function testUpdateRatesThrowsExceptionOnInvalidJson(): void
    {
        $this->setupHttpMocksWithRawResponse('invalid json');

        $this->expectException(ExchangeRateApiException::class);
        $this->expectExceptionMessage('Failed to parse API response');

        $this->fetcher->updateRates();
    }

    public function testUpdateRatesThrowsExceptionOnApiError(): void
    {
        $responseData = [
            'result' => 'error',
            'error' => 'Invalid API key',
        ];

        $this->setupHttpMocks($responseData);

        $this->expectException(ExchangeRateApiException::class);
        $this->expectExceptionMessage('API returned error: Invalid API key');

        $this->fetcher->updateRates();
    }

    public function testUpdateRatesThrowsExceptionOnMissingConversionRates(): void
    {
        $responseData = [
            'result' => 'success',
        ];

        $this->setupHttpMocks($responseData);

        $this->expectException(ExchangeRateApiException::class);
        $this->expectExceptionMessage('Missing or invalid conversion rates in API response');

        $this->fetcher->updateRates();
    }

    public function testUpdateRatesThrowsExceptionOnInvalidRateFormat(): void
    {
        $responseData = [
            'result' => 'success',
            'conversion_rates' => [
                'USD' => 'invalid',
            ],
        ];

        $this->setupHttpMocks($responseData);

        $this->expectException(ExchangeRateApiException::class);
        $this->expectExceptionMessage('Invalid rate format in API response');

        $this->fetcher->updateRates();
    }

    /**
     * @param array<string, string|float|array<string, string|float>> $responseData
     */
    private function setupHttpMocks(array $responseData): void
    {
        $jsonResponse = json_encode($responseData);
        if (false === $jsonResponse) {
            throw new \RuntimeException('Failed to encode response data as JSON');
        }
        $this->setupHttpMocksWithRawResponse($jsonResponse);
    }

    private function setupHttpMocksWithRawResponse(string|false $rawResponse): void
    {
        $this->requestFactory->expects(self::once())
            ->method('createRequest')
            ->with('GET', self::API_HOST.self::API_KEY.'/latest/'.self::BASE_CURRENCY)
            ->willReturn($this->request);

        $this->httpClient->expects(self::once())
            ->method('sendRequest')
            ->with($this->request)
            ->willReturn($this->response);

        $this->response->expects(self::once())
            ->method('getStatusCode')
            ->willReturn(200);

        $this->response->expects(self::once())
            ->method('getBody')
            ->willReturn($this->stream);

        $this->stream->expects(self::once())
            ->method('__toString')
            ->willReturn($rawResponse);
    }
}
