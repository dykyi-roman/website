<?php

declare(strict_types=1);

namespace Site\Registration\DomainModel\Service;

use Shared\DomainModel\ValueObject\Country;

interface CountryDetectorInterface
{
    public function detect(): ?Country;
}
