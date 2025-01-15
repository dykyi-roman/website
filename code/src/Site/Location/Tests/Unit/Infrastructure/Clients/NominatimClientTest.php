<?php

declare(strict_types=1);

namespace Site\Location\Tests\Unit\Infrastructure\Clients;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;
use Psr\Log\LoggerInterface;
use Shared\DomainModel\ValueObject\City;
use Shared\DomainModel\ValueObject\Country;
use Shared\DomainModel\ValueObject\Location;
use Site\Location\Infrastructure\Clients\NominatimClient;

#[CoversClass(NominatimClient::class)]
final class NominatimClientTest extends TestCase
{
    /** @var ClientInterface&MockObject */
    private ClientInterface $client;

    /** @var LoggerInterface&MockObject */
    private LoggerInterface $logger;

    /** @var RequestFactoryInterface&MockObject */
    private RequestFactoryInterface $requestFactory;

    /** @var UriFactoryInterface&MockObject */
    private UriFactoryInterface $uriFactory;

    private NominatimClient $nominatimClient;

    protected function setUp(): void
    {
        $this->client = $this->getMockBuilder(ClientInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->requestFactory = $this->getMockBuilder(RequestFactoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->uriFactory = $this->getMockBuilder(UriFactoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->nominatimClient = new NominatimClient(
            'http://nominatim.openstreetmap.org',
            $this->client,
            $this->logger,
            $this->requestFactory,
            $this->uriFactory,
        );
    }

    public function testLocationByCoordinatesSuccessfulResponse(): void
    {
        /** @var UriInterface&MockObject */
        $uri = $this->getMockBuilder(UriInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $uri->method('withQuery')
            ->willReturn($uri);

        $this->uriFactory->method('createUri')
            ->with('http://nominatim.openstreetmap.org')
            ->willReturn($uri);

        /** @var RequestInterface&MockObject */
        $request = $this->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $request->method('withHeader')
            ->willReturn($request);

        $this->requestFactory->method('createRequest')
            ->with('GET', $uri)
            ->willReturn($request);

        /** @var ResponseInterface&MockObject */
        $response = $this->getMockBuilder(ResponseInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $response->method('getStatusCode')
            ->willReturn(200);

        /** @var \Psr\Http\Message\StreamInterface&MockObject */
        $stream = $this->getMockBuilder(\Psr\Http\Message\StreamInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $stream->method('__toString')
            ->willReturn(json_encode([
                'address' => [
                    'city' => 'Test City',
                    'country_code' => 'us',
                    'house_number' => '123',
                    'road' => 'Test Road',
                    'suburb' => 'Test Suburb',
                ],
            ]));

        $response->method('getBody')
            ->willReturn($stream);

        $this->client->method('sendRequest')
            ->with($request)
            ->willReturn($response);

        $result = $this->nominatimClient->locationByCoordinates('40.7128', '-74.0060');

        self::assertInstanceOf(Location::class, $result);
        self::assertInstanceOf(Country::class, $result->country);
        self::assertInstanceOf(City::class, $result->city);
        self::assertEquals('us', $result->country->code);
        self::assertEquals('Test City', $result->city->name);
        self::assertEquals('Test City', $result->city->transcription);
        self::assertEquals('123 Test Road, Test Suburb', $result->city->address);
    }

    public function testLocationByCoordinatesFailedResponse(): void
    {
        /** @var UriInterface&MockObject */
        $uri = $this->getMockBuilder(UriInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $uri->method('withQuery')
            ->willReturn($uri);

        $this->uriFactory->method('createUri')
            ->willReturn($uri);

        /** @var RequestInterface&MockObject */
        $request = $this->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $request->method('withHeader')
            ->willReturn($request);

        $this->requestFactory->method('createRequest')
            ->willReturn($request);

        /** @var ResponseInterface&MockObject */
        $response = $this->getMockBuilder(ResponseInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $response->method('getStatusCode')
            ->willReturn(400);

        $this->client->method('sendRequest')
            ->willReturn($response);

        $this->logger->method('error')
            ->with('Nominatim API request failed');

        $result = $this->nominatimClient->locationByCoordinates('40.7128', '-74.0060');

        self::assertInstanceOf(Location::class, $result);
        self::assertNull($result->country);
        self::assertNull($result->city);
    }

    public function testLocationByCoordinatesInvalidJsonResponse(): void
    {
        /** @var UriInterface&MockObject */
        $uri = $this->getMockBuilder(UriInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $uri->method('withQuery')
            ->willReturn($uri);

        $this->uriFactory->method('createUri')
            ->willReturn($uri);

        /** @var RequestInterface&MockObject */
        $request = $this->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $request->method('withHeader')
            ->willReturn($request);

        $this->requestFactory->method('createRequest')
            ->willReturn($request);

        /** @var ResponseInterface&MockObject */
        $response = $this->getMockBuilder(ResponseInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $response->method('getStatusCode')
            ->willReturn(200);

        /** @var \Psr\Http\Message\StreamInterface&MockObject */
        $stream = $this->getMockBuilder(\Psr\Http\Message\StreamInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $stream->method('__toString')
            ->willReturn('invalid json');

        $response->method('getBody')
            ->willReturn($stream);

        $this->client->method('sendRequest')
            ->willReturn($response);

        $this->logger->method('error')
            ->with('Failed to parse Nominatim API response');

        $result = $this->nominatimClient->locationByCoordinates('40.7128', '-74.0060');

        self::assertInstanceOf(Location::class, $result);
        self::assertNull($result->country);
        self::assertNull($result->city);
    }
}
