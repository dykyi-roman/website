<?php

declare(strict_types=1);

namespace Site\Registration\Tests\Unit\Presentation\Web\Twig;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shared\DomainModel\Services\UserFetcherInterface;
use Site\Registration\Presentation\Web\Twig\RegistrationExtension;

#[CoversClass(RegistrationExtension::class)]
final class RegistrationExtensionTest extends TestCase
{
    public function testGetGlobalsWhenUserIsNotLoggedIn(): void
    {
        $userFetcher = $this->createMock(UserFetcherInterface::class);
        $userFetcher->expects($this->once())
            ->method('isLogin')
            ->willReturn(false);

        $supportedCountries = ['US', 'UK'];
        $extension = new RegistrationExtension($userFetcher, $supportedCountries);

        $globals = $extension->getGlobals();

        $this->assertArrayHasKey('countries', $globals);
        $this->assertCount(2, $globals['countries']);
        $this->assertEquals(['code' => 'US'], $globals['countries'][0]);
        $this->assertEquals(['code' => 'UK'], $globals['countries'][1]);
    }

    public function testGetGlobalsWhenUserIsLoggedIn(): void
    {
        $userFetcher = $this->createMock(UserFetcherInterface::class);
        $userFetcher->expects($this->once())
            ->method('isLogin')
            ->willReturn(true);

        $supportedCountries = ['US', 'UK'];
        $extension = new RegistrationExtension($userFetcher, $supportedCountries);

        $globals = $extension->getGlobals();

        $this->assertEmpty($globals);
    }
}
