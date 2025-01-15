<?php

declare(strict_types=1);

namespace Site\Location\Tests\Unit\DomainModel\Dto;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Site\Location\DomainModel\Dto\CityDto;

#[CoversClass(CityDto::class)]
final class CityDtoTest extends TestCase
{
    public function testConstruction(): void
    {
        $dto = new CityDto(
            name: 'New York',
            transcription: 'New York',
            area: 'NY'
        );

        $this->assertSame('New York', $dto->name);
        $this->assertSame('New York', $dto->transcription);
        $this->assertSame('NY', $dto->area);
    }

    public function testJsonSerialization(): void
    {
        $dto = new CityDto(
            name: 'Los Angeles',
            transcription: 'Los Angeles',
            area: 'CA'
        );

        $expected = [
            'name' => 'Los Angeles',
            'transcription' => 'Los Angeles',
            'address' => 'CA',
        ];

        $this->assertSame($expected, $dto->jsonSerialize());
        $this->assertSame(json_encode($expected), json_encode($dto));
    }
}
