<?php

declare(strict_types=1);

namespace Site\Locale\DomainModel\Service;

use Symfony\Component\HttpFoundation\Request;

interface LocaleResolverInterface
{
    public function resolve(Request $request): ?string;
}
