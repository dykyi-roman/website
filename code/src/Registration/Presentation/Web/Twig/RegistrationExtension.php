<?php

declare(strict_types=1);

namespace App\Registration\Presentation\Web\Twig;

use Symfony\Bundle\SecurityBundle\Security;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

final class RegistrationExtension extends AbstractExtension implements GlobalsInterface
{
    /**
     * @param array<string, string> $supportedCountries
     */
    public function __construct(
        private readonly Security $security,
        private readonly array $supportedCountries,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function getGlobals(): array
    {
        $response = [];
        $user = $this->security->getUser();
        if (!$user) {
            $countries = [];
            foreach ($this->supportedCountries as $code) {
                $countries[] = ['code' => $code];
            }
            $response['countries'] = $countries;
        }

        return $response;
    }
}
