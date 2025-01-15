<?php

declare(strict_types=1);

namespace Site\Locale\Tests\Unit\DomainModel\Service\LocaleResolver;

use GeoIp2\Model\Country;
use GeoIp2\ProviderInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Site\Locale\DomainModel\Service\LocaleResolver\GeoIPLocaleResolver;
use Symfony\Component\HttpFoundation\Request;

#[CoversClass(GeoIPLocaleResolver::class)]
final class GeoIPLocaleResolverTest extends TestCase
{
    /** @var ProviderInterface&MockObject */
    private ProviderInterface $geoIpReader;
    /** @var array<string, string> */
    private array $countryToLocaleMap;
    private GeoIPLocaleResolver $resolver;

    protected function setUp(): void
    {
        $this->geoIpReader = $this->createMock(ProviderInterface::class);
        $this->countryToLocaleMap = [
            'US' => 'en',
            'FR' => 'fr',
            'DE' => 'de',
        ];
        $this->resolver = new GeoIPLocaleResolver($this->geoIpReader, $this->countryToLocaleMap);
    }

    public function testReturnsNullWhenNoIpPresent(): void
    {
        $request = Request::create('/');

        $this->assertNull($this->resolver->resolve($request));
    }

    public function testReturnsNullForLocalhost(): void
    {
        $request = Request::create('/');
        $request->server->set('REMOTE_ADDR', '127.0.0.1');

        $this->assertNull($this->resolver->resolve($request));
    }

    public function testReturnsLocaleForKnownCountry(): void
    {
        $request = Request::create('/');
        $request->server->set('REMOTE_ADDR', '203.0.113.1');

        $record = new Country([
            'country' => [
                'iso_code' => 'FR',
            ],
        ]);

        $this->geoIpReader
            ->expects($this->once())
            ->method('country')
            ->with('203.0.113.1')
            ->willReturn($record);

        $this->assertSame('fr', $this->resolver->resolve($request));
    }

    public function testReturnsNullForUnknownCountry(): void
    {
        $request = Request::create('/');
        $request->server->set('REMOTE_ADDR', '203.0.113.1');

        $record = new Country([
            'country' => [
                'iso_code' => 'JP',
            ],
        ]);

        $this->geoIpReader
            ->expects($this->once())
            ->method('country')
            ->with('203.0.113.1')
            ->willReturn($record);

        $this->assertNull($this->resolver->resolve($request));
    }

    public function testReturnsNullWhenGeoIpThrowsException(): void
    {
        $request = Request::create('/');
        $request->server->set('REMOTE_ADDR', '203.0.113.1');

        $this->geoIpReader
            ->expects($this->once())
            ->method('country')
            ->with('203.0.113.1')
            ->willThrowException(new \Exception('GeoIP error'));

        $this->assertNull($this->resolver->resolve($request));
    }
}
