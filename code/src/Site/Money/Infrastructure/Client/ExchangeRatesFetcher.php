<?php

namespace Site\Money\Infrastructure\Client;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Site\Money\DomainModel\Exception\ExchangeRateApiException;

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
     * @return array<string, float>
     *
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

            if (!is_array($data)) {
                throw new ExchangeRateApiException('Invalid API response format');
            }

            if (!isset($data['result']) || 'success' !== $data['result']) {
                $error = isset($data['error']) && is_string($data['error'])
                    ? $data['error']
                    : 'Unknown error';
                throw new ExchangeRateApiException('API returned error: '.$error);
            }

            if (!isset($data['conversion_rates']) || !is_array($data['conversion_rates'])) {
                throw new ExchangeRateApiException('Missing or invalid conversion rates in API response');
            }

            $rates = [];
            foreach ($data['conversion_rates'] as $currency => $rate) {
                if (!is_string($currency) || !is_numeric($rate)) {
                    throw new ExchangeRateApiException('Invalid rate format in API response');
                }
                $rates[$currency] = (float) $rate;
            }

            return $rates;
        } catch (\Throwable $exception) {
            throw new ExchangeRateApiException('Failed to fetch exchange rates: '.$exception->getMessage());
        }
    }
}
