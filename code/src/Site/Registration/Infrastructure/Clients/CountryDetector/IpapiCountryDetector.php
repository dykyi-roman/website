<?php

declare(strict_types=1);

namespace Site\Registration\Infrastructure\Clients\CountryDetector;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Log\LoggerInterface;
use Site\Registration\DomainModel\Service\CountryDetectorInterface;
use Shared\DomainModel\ValueObject\Country;

/** !!! Have a request limits !!! */
final readonly class IpapiCountryDetector implements CountryDetectorInterface
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

            $data = json_decode((string)$response->getBody(), true);
            if (!is_array($data) || !isset($data['country_code']) || empty($data['country_code'])) {
                return null;
            }

            return new Country(strtoupper($data['country_code']));
        } catch (\Throwable $exception) {
            $this->logger->error($exception->getMessage());

            return null;
        }
    }
}
