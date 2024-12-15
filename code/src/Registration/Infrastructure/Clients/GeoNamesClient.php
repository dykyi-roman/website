<?php

declare(strict_types=1);

namespace App\Registration\Infrastructure\Clients;

use App\Registration\DomainModel\Service\DictionaryOfCitiesInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use JsonException;
use Psr\Log\LoggerInterface;

final readonly class GeoNamesClient implements DictionaryOfCitiesInterface
{
    public function __construct(
        private string $apiGeoUserName,
        private string $apiGeoHost,
        private ClientInterface $client,
        private LoggerInterface $logger,
        private RequestFactoryInterface $requestFactory,
        private UriFactoryInterface $uriFactory,
    ) {
    }

    public function cityByCountryAndLocale(string $countryCode, string $lang, string $city): array
    {
        try {
            $uri = $this->uriFactory->createUri($this->apiGeoHost)
                ->withQuery(http_build_query([
                    'country' => strtoupper($countryCode),
                    'lang' => $lang,
                    'name_startsWith' => $city,
                    'featureClass' => 'P',
                    'maxRows' => 100,
                    'username' => $this->apiGeoUserName,
                ]));

            $request = $this->requestFactory->createRequest('GET', $uri);
            $response = $this->client->sendRequest($request);

            if ($response->getStatusCode() !== 200) {
                $this->logger->error('GeoNames API request failed', [
                    'status_code' => $response->getStatusCode(),
                    'country' => $countryCode,
                    'lang' => $lang,
                    'city' => $city,
                ]);
                return [];
            }

            $content = $response->getBody()->getContents();
            if (empty($content)) {
                $this->logger->warning('Empty response from GeoNames API', [
                    'country' => $countryCode,
                    'lang' => $lang,
                    'city' => $city,
                ]);
                return [];
            }

            return json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            $this->logger->error('Failed to decode GeoNames API response', [
                'error' => $exception->getMessage(),
                'country' => $countryCode,
                'lang' => $lang,
                'city' => $city,
            ]);

            return [];
        } catch (\Throwable $exception) {
            $this->logger->error('GeoNames API request error', [
                'error' => $exception->getMessage(),
                'country' => $countryCode,
                'lang' => $lang,
                'city' => $city,
            ]);

            return [];
        }
    }
}
