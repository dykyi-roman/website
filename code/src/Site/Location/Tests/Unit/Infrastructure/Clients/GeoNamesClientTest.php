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
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;
use Psr\Log\LoggerInterface;
use Site\Location\DomainModel\Dto\CityDto;
use Site\Location\Infrastructure\Clients\GeoNamesClient;

#[CoversClass(GeoNamesClient::class)]
final class GeoNamesClientTest extends TestCase
{
    /** @var ClientInterface&MockObject */
    private ClientInterface $client;

    /** @var LoggerInterface&MockObject */
    private LoggerInterface $logger;

    /** @var RequestFactoryInterface&MockObject */
    private RequestFactoryInterface $requestFactory;

    /** @var UriFactoryInterface&MockObject */
    private UriFactoryInterface $uriFactory;

    private GeoNamesClient $geoNamesClient;

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

        $this->geoNamesClient = new GeoNamesClient(
            'test_username',
            'http://api.geonames.org',
            $this->client,
            $this->logger,
            $this->requestFactory,
            $this->uriFactory,
        );
    }

    public function testCityByCountryAndLocaleSuccessfulResponse(): void
    {
        /** @var UriInterface&MockObject */
        $uri = $this->getMockBuilder(UriInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $uri->method('withQuery')
            ->willReturn($uri);

        $this->uriFactory->method('createUri')
            ->with('http://api.geonames.org')
            ->willReturn($uri);

        /** @var RequestInterface&MockObject */
        $request = $this->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->requestFactory->method('createRequest')
            ->with('GET', $uri)
            ->willReturn($request);

        /** @var ResponseInterface&MockObject */
        $response = $this->getMockBuilder(ResponseInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $response->method('getStatusCode')
            ->willReturn(200);

        /** @var StreamInterface&MockObject */
        $stream = $this->getMockBuilder(StreamInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $stream->method('__toString')
            ->willReturn(json_encode([
                'geonames' => [
                    [
                        'name' => 'Test City',
                        'toponymName' => 'Test City Trans',
                        'adminName1' => 'Test Area',
                    ],
                ],
            ]));

        $response->method('getBody')
            ->willReturn($stream);

        $this->client->method('sendRequest')
            ->with($request)
            ->willReturn($response);

        $result = $this->geoNamesClient->cityByCountryAndLocale('US', 'en', 'Test');

        self::assertCount(1, $result);
        self::assertInstanceOf(CityDto::class, $result[0]);
        self::assertEquals('Test City', $result[0]->name);
        self::assertEquals('Test City Trans', $result[0]->transcription);
        self::assertEquals('Test Area', $result[0]->area);
    }

    public function testCityByCountryAndLocaleFailedResponse(): void
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
            ->with('GeoNames API request failed');

        $result = $this->geoNamesClient->cityByCountryAndLocale('US', 'en', 'Test');

        self::assertEmpty($result);
    }

    public function testCityByCountryAndLocaleInvalidJsonResponse(): void
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

        $this->requestFactory->method('createRequest')
            ->willReturn($request);

        /** @var ResponseInterface&MockObject */
        $response = $this->getMockBuilder(ResponseInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $response->method('getStatusCode')
            ->willReturn(200);

        /** @var StreamInterface&MockObject */
        $stream = $this->getMockBuilder(StreamInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $stream->method('__toString')
            ->willReturn('invalid json');

        $response->method('getBody')
            ->willReturn($stream);

        $this->client->method('sendRequest')
            ->willReturn($response);

        $this->logger->method('error')
            ->with('Failed to parse GeoNames API response');

        $result = $this->geoNamesClient->cityByCountryAndLocale('US', 'en', 'Test');

        self::assertEmpty($result);
    }
}
