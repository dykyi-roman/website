<?php

declare(strict_types=1);

namespace App\Locale\DomainModel\Service\LocaleResolver;

use App\Locale\DomainModel\Service\LocaleResolverInterface;
use Symfony\Component\HttpFoundation\Request;

final readonly class DefaultLocaleResolver implements LocaleResolverInterface
{
    public function __construct(
        private string $defaultLocale,
    ) {
    }

    public function resolve(Request $request): ?string
    {
        return $this->defaultLocale;
    }
}