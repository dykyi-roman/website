<?php

declare(strict_types=1);

namespace Site\Locale\DomainModel\Service\LocaleResolver;

use Site\Locale\DomainModel\Service\LocaleResolverInterface;
use Symfony\Component\HttpFoundation\Request;

final readonly class AcceptLanguageLocaleResolver implements LocaleResolverInterface
{
    public function resolve(Request $request): ?string
    {
        $acceptLanguage = $request->headers->get('Accept-Language');
        if (!$acceptLanguage) {
            return null;
        }

        $locales = explode(',', $acceptLanguage);
        foreach ($locales as $locale) {
            $parts = explode(';', $locale);
            if (!empty($parts[0])) {
                return strtolower(substr($parts[0], 0, 2));
            }
        }

        return null;
    }
}
