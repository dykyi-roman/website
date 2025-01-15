<?php

declare(strict_types=1);

namespace Site\Location\Tests\Unit\Application\CitiesDictionary\Query;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Site\Location\Application\CitiesDictionary\Query\GetCitiesDictionaryQuery;
use Site\Location\Application\CitiesDictionary\Query\GetCitiesDictionaryQueryHandler;
use Site\Location\DomainModel\Dto\CityDto;
use Site\Location\DomainModel\Service\DictionaryOfCitiesInterface;

#[CoversClass(GetCitiesDictionaryQueryHandler::class)]
final class GetCitiesDictionaryQueryHandlerTest extends TestCase
{
    /** @var DictionaryOfCitiesInterface&MockObject */
    private DictionaryOfCitiesInterface $dictionaryOfCities;
    private GetCitiesDictionaryQueryHandler $handler;

    protected function setUp(): void
    {
        $this->dictionaryOfCities = $this->getMockBuilder(DictionaryOfCitiesInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->handler = new GetCitiesDictionaryQueryHandler($this->dictionaryOfCities);
    }

    public function testInvoke(): void
    {
        $query = new GetCitiesDictionaryQuery(
            countryCode: 'US',
            lang: 'en',
            city: 'New York'
        );

        $expectedCity = new CityDto(
            name: 'New York',
            transcription: 'New York',
            area: 'NY'
        );

        $this->dictionaryOfCities
            ->method('cityByCountryAndLocale')
            ->with($query->countryCode, $query->lang, $query->city)
            ->willReturn([$expectedCity]);

        $result = $this->handler->__invoke($query);

        $this->assertCount(1, $result);
        $this->assertEquals($expectedCity, $result[0]);
    }
}
