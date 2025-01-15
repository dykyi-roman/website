<?php

declare(strict_types=1);

namespace Site\Location\Tests\Unit\Application\DetectLocation\Query;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shared\DomainModel\ValueObject\City;
use Shared\DomainModel\ValueObject\Country;
use Shared\DomainModel\ValueObject\Location;
use Site\Location\Application\DetectLocation\Query\DetectLocationQuery;
use Site\Location\Application\DetectLocation\Query\DetectLocationQueryHandler;
use Site\Location\DomainModel\Service\GeoLocationInterface;

#[CoversClass(DetectLocationQueryHandler::class)]
final class DetectLocationQueryHandlerTest extends TestCase
{
    /** @var GeoLocationInterface&MockObject */
    private GeoLocationInterface $geoLocation;
    private DetectLocationQueryHandler $handler;

    protected function setUp(): void
    {
        $this->geoLocation = $this->getMockBuilder(GeoLocationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->handler = new DetectLocationQueryHandler($this->geoLocation);
    }

    public function testInvoke(): void
    {
        $query = new DetectLocationQuery(
            latitude: '40.7128',
            longitude: '-74.0060'
        );

        $expectedLocation = new Location(
            country: new Country('US'),
            city: new City(
                name: 'New York',
                transcription: 'New York',
                address: 'New York, NY, USA'
            )
        );

        $this->geoLocation
            ->method('locationByCoordinates')
            ->with($query->latitude, $query->longitude)
            ->willReturn($expectedLocation);

        $result = $this->handler->__invoke($query);

        $this->assertSame($expectedLocation, $result);
    }
}
