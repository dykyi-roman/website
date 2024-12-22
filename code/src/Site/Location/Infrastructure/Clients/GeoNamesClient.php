<?php

declare(strict_types=1);

namespace Site\Location\Infrastructure\Clients;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Log\LoggerInterface;
use Site\Location\DomainModel\Dto\CityDto;
use Site\Location\DomainModel\Service\DictionaryOfCitiesInterface;

final readonly class GeoNamesClient implements DictionaryOfCitiesInterface
{
    private const int MAX_ROWS = 10;

    public function __construct(
        private string $apiGeoUserName,
        private string $apiGeoHost,
        private ClientInterface $client,
        private LoggerInterface $logger,
        private RequestFactoryInterface $requestFactory,
        private UriFactoryInterface $uriFactory,
    ) {
    }

    /**
     * @return CityDto[]
     */
    public function cityByCountryAndLocale(
        string $countryCode,
        string $lang,
        string $city,
        int $maxRows = self::MAX_ROWS,
    ): array {
        try {
            $uri = $this->uriFactory->createUri($this->apiGeoHost)
                ->withQuery(http_build_query([
                    'country' => strtoupper($countryCode),
                    'lang' => $lang,
                    'name_startsWith' => $city,
                    'featureClass' => 'P',
                    'maxRows' => $maxRows,
                    'username' => $this->apiGeoUserName,
                ]));

            $request = $this->requestFactory->createRequest('GET', $uri);
            $response = $this->client->sendRequest($request);

            if (200 !== $response->getStatusCode()) {
                $this->logger->error('GeoNames API request failed', [
                    'status_code' => $response->getStatusCode(),
                    'country' => $countryCode,
                    'lang' => $lang,
                    'city' => $city,
                ]);

                return [];
            }

            $data = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);

            return array_map(
                static fn (array $city): CityDto => new CityDto(
                    countryCode: $city['countryCode'],
                    name: $city['name'],
                    transcription: $city['toponymName'] ?? '',
                    area: $city['adminName1'] ?? '',
                ),
                $data['geonames'] ?? []
            );
        } catch (\JsonException $exception) {
            $this->logger->error('Failed to parse GeoNames API response', [
                'error' => $exception->getMessage(),
                'country' => $countryCode,
                'lang' => $lang,
                'city' => $city,
            ]);

            return [];
        } catch (\Throwable $exception) {
            $this->logger->error('GeoNames API request failed', [
                'error' => $exception->getMessage(),
                'country' => $countryCode,
                'lang' => $lang,
                'city' => $city,
            ]);

            return [];
        }
    }
}
