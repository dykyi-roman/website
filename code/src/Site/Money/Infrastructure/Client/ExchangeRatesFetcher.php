<?php

namespace Site\Money\Infrastructure\Client;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Site\Money\Domain\Exception\ExchangeRateApiException;

final readonly class ExchangeRatesFetcher implements ExchangeRatesFetcherInterface
{
    public function __construct(
        private ClientInterface $httpClient,
        private RequestFactoryInterface $requestFactory,
        private string $exchangeRateApiHost,
        private string $exchangeRateApiKey,
        private string $exchangeRateBaseCurrency,
    ) {
    }

    /**
     * @throws ExchangeRateApiException
     */
    public function updateRates(): array
    {
        try {
            $request = $this->requestFactory->createRequest(
                'GET',
                $this->exchangeRateApiHost.$this->exchangeRateApiKey.'/latest/'.$this->exchangeRateBaseCurrency
            );

            $response = $this->httpClient->sendRequest($request);
            $statusCode = $response->getStatusCode();

            if (200 !== $statusCode) {
                throw new ExchangeRateApiException("API request failed with status code: {$statusCode}");
            }

            $data = json_decode((string) $response->getBody(), true);

            if (JSON_ERROR_NONE !== json_last_error()) {
                throw new ExchangeRateApiException('Failed to parse API response');
            }

            if ('success' !== $data['result']) {
                throw new ExchangeRateApiException('API returned error: '.($data['error'] ?? 'Unknown error'));
            }

            return $data['conversion_rates'];
        } catch (\Throwable $exception) {
            throw new ExchangeRateApiException('Failed to fetch exchange rates: '.$exception->getMessage());
        }
    }
}
