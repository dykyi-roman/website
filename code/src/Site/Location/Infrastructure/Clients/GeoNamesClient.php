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
            if (!is_array($data)) {
                throw new \JsonException('Invalid JSON structure');
            }

            $geonames = $data['geonames'] ?? null;
            if (!is_array($geonames)) {
                return [];
            }

            return array_map(
                static function (mixed $city): CityDto {
                    if (!is_array($city)) {
                        return new CityDto('', '', '', '');
                    }

                    $countryCode = isset($city['countryCode']) && is_string($city['countryCode']) ? $city['countryCode'] : '';
                    $name = isset($city['name']) && is_string($city['name']) ? $city['name'] : '';
                    $transcription = isset($city['toponymName']) && is_string($city['toponymName']) ? $city['toponymName'] : '';
                    $area = isset($city['adminName1']) && is_string($city['adminName1']) ? $city['adminName1'] : '';

                    return new CityDto($countryCode, $name, $transcription, $area);
                },
                $geonames
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
