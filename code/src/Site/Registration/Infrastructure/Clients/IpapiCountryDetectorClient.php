<?php

declare(strict_types=1);

namespace Site\Registration\Infrastructure\Clients;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Log\LoggerInterface;
use Shared\DomainModel\ValueObject\Country;
use Site\Registration\DomainModel\Service\CountryDetectorInterface;

/**
 * !!! Have a request limits !!!
 *
 * Website: https://ipapi.co
 */
final readonly class IpapiCountryDetectorClient implements CountryDetectorInterface
{
    public function __construct(
        private ClientInterface $client,
        private LoggerInterface $logger,
        private RequestFactoryInterface $requestFactory,
        private string $apiapiHost,
    ) {
    }

    public function detect(): ?Country
    {
        try {
            $request = $this->requestFactory->createRequest('GET', $this->apiapiHost);
            $response = $this->client->sendRequest($request);
            if (200 !== $response->getStatusCode()) {
                return null;
            }

            $data = json_decode((string) $response->getBody(), true);
            if (!is_array($data) || !isset($data['country_code']) || empty($data['country_code']) || !is_string($data['country_code'])) {
                return null;
            }

            return new Country(strtoupper($data['country_code']));
        } catch (\Throwable $exception) {
            $this->logger->error($exception->getMessage());

            return null;
        }
    }
}
