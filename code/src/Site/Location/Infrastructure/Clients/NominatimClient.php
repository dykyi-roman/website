<?php

declare(strict_types=1);

namespace Site\Location\Infrastructure\Clients;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Log\LoggerInterface;
use Shared\DomainModel\ValueObject\City;
use Shared\DomainModel\ValueObject\Country;
use Shared\DomainModel\ValueObject\Location;
use Site\Location\DomainModel\Service\GeoLocationInterface;

final readonly class NominatimClient implements GeoLocationInterface
{
    public function __construct(
        private string $apiApiNominatimHost,
        private ClientInterface $client,
        private LoggerInterface $logger,
        private RequestFactoryInterface $requestFactory,
        private UriFactoryInterface $uriFactory,
    ) {
    }

    public function locationByCoordinates(string $latitude, string $longitude): Location
    {
        try {
            $uri = $this->uriFactory->createUri($this->apiApiNominatimHost)
                ->withQuery(http_build_query([
                    'format' => 'json',
                    'lat' => $latitude,
                    'lon' => $longitude,
                ]));

            $request = $this->requestFactory->createRequest('GET', $uri);
            $response = $this->client->sendRequest($request);

            if (200 !== $response->getStatusCode()) {
                $this->logger->error('Nominatim API request failed', [
                    'status_code' => $response->getStatusCode(),
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                ]);

                return new Location(null, null);
            }

            $data = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
            if (!is_array($data)) {
                throw new \JsonException('Invalid JSON structure');
            }

            $address = $data['address'] ?? [];
            $city = $address['city'] ?? $address['town'] ?? $address['village'] ?? '';

            return new Location(
                new Country(trim($address['country_code'])),
                new City(trim($city), trim($city)),
            );
        } catch (\JsonException $exception) {
            $this->logger->error('Failed to parse Nominatim API response', [
                'error' => $exception->getMessage(),
                'latitude' => $latitude,
                'longitude' => $longitude,
            ]);

            return new Location(null, null);
        } catch (\Throwable $exception) {
            $this->logger->error('Nominatim API request failed', [
                'error' => $exception->getMessage(),
                'latitude' => $latitude,
                'longitude' => $longitude,
            ]);

            return new Location(null, null);
        }
    }
}