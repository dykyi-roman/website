<?php

declare(strict_types=1);

namespace App\Dashboard\Presentation\Web\Twig;

use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

final class CountriesExtension extends AbstractExtension implements GlobalsInterface
{
    /**
     * @return array<string, mixed>
     */
    public function getGlobals(): array
    {
        return [
            'countries' => [
                ['code' => 'ua', 'name' => 'Ukraine'],
                ['code' => 'es', 'name' => 'Spain'],
            ],
        ];
    }
}
