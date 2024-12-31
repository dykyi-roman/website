<?php

declare(strict_types=1);

namespace Site\Registration\Presentation\Web\Twig;

use Site\User\DomainModel\Service\UserFetcher;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

final class RegistrationExtension extends AbstractExtension implements GlobalsInterface
{
    /**
     * @param array<string, string> $supportedCountries
     */
    public function __construct(
        private readonly UserFetcher $userFetcher,
        private readonly array $supportedCountries,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function getGlobals(): array
    {
        $response = [];
        if (!$this->userFetcher->logined()) {
            $countries = [];
            foreach ($this->supportedCountries as $code) {
                $countries[] = ['code' => $code];
            }
            $response['countries'] = $countries;
        }

        return $response;
    }
}
