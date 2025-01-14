<?php

declare(strict_types=1);

namespace Site\Registration\Presentation\Web\Twig;

use Profile\User\DomainModel\Service\UserFetcherInterface;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

final class RegistrationExtension extends AbstractExtension implements GlobalsInterface
{
    /**
     * @param array<string, string> $supportedCountries
     */
    public function __construct(
        private readonly UserFetcherInterface $userFetcher,
        private readonly array $supportedCountries,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function getGlobals(): array
    {
        $response = [];
        if (!$this->userFetcher->isLogin()) {
            $countries = [];
            foreach ($this->supportedCountries as $code) {
                $countries[] = ['code' => $code];
            }
            $response['countries'] = $countries;
        }

        return $response;
    }
}
