<?php

declare(strict_types=1);

namespace App\Locale\Presentation\Web\Twig;

use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

final class LocaleExtension extends AbstractExtension implements GlobalsInterface
{
    /**
     * @param array<string, string> $supportedLocales
     */
    public function __construct(
        private readonly array $supportedLocales,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function getGlobals(): array
    {
        $locales = [];
        foreach ($this->supportedLocales as $code) {
            $locales[] = ['code' => $code];
        }

        return ['locales' => $locales];
    }
}
