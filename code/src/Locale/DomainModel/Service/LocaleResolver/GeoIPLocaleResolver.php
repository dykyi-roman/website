<?php

declare(strict_types=1);

namespace App\Locale\DomainModel\Service\LocaleResolver;

use App\Locale\DomainModel\Service\LocaleResolverInterface;
use GeoIp2\ProviderInterface;
use Symfony\Component\HttpFoundation\Request;

final readonly class GeoIPLocaleResolver implements LocaleResolverInterface
{
    private const string LOCAL_HOST = '127.0.0.1';

    public function __construct(
        private ProviderInterface $geoIpReader,
        private array $countryToLocaleMap,
    ) {
    }

    public function resolve(Request $request): ?string
    {
        $ip = $request->getClientIp();
        if (!$ip || $ip === self::LOCAL_HOST) {
            return null;
        }

        try {
            $record = $this->geoIpReader->country($ip);
            $countryCode = $record->country->isoCode;

            return $this->countryToLocaleMap[$countryCode] ?? null;
        } catch (\Throwable) {
            return null;
        }
    }
}