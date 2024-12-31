<?php

declare(strict_types=1);

namespace Site\Money\Presentation\Web\Twig;

use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

final class CurrencyExtension extends AbstractExtension implements GlobalsInterface
{
    /**
     * @param array<string, string> $supportedCurrencies
     */
    public function __construct(
        private readonly string $defaultCurrency,
        private readonly array $supportedCurrencies,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function getGlobals(): array
    {
        $currencies = [];
        foreach ($this->supportedCurrencies as $code => $symbol) {
            $currencies[] = ['code' => $code, 'symbol' => $symbol];
        }

        return [
            'default_currency' => $this->defaultCurrency,
            'currencies' => $currencies,
        ];
    }
}
