<?php

declare(strict_types=1);

namespace App\Locale\Presentation\Web\Twig;

use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

final class LocaleExtension extends AbstractExtension implements GlobalsInterface
{
    /**
     * @param array<string, string> $supportedLanguages
     */
    public function __construct(
        private readonly array $supportedLanguages,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function getGlobals(): array
    {
        $languages = [];
        foreach ($this->supportedLanguages as $code => $name) {
            $languages[] = ['code' => $code, 'name' => $name];
        }

        return ['supported_languages' => $languages];
    }
}
