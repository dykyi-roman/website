<?php

declare(strict_types=1);

namespace Site\Locale\DomainModel\Service\LocaleResolver;

use Site\Locale\DomainModel\Service\LocaleResolverInterface;
use Symfony\Component\HttpFoundation\Request;

final readonly class QueryParameterLocaleResolver implements LocaleResolverInterface
{
    public function resolve(Request $request): ?string
    {
        return $request->query->get('lang');
    }
}
