<?php

declare(strict_types=1);

namespace App\Locale\DomainModel\Service;

use Symfony\Component\HttpFoundation\Request;

final readonly class ChainLocaleResolver implements LocaleResolverInterface
{
    public function __construct(
        private iterable $resolvers,
    ) {
    }

    public function resolve(Request $request): ?string
    {
        foreach ($this->resolvers as $resolver) {
            $locale = $resolver->resolve($request);
            if (null !== $locale) {
                return $locale;
            }
        }

        return null;
    }
}
