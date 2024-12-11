<?php

declare(strict_types=1);

namespace App\Dashboard\Presentation\Web\Twig;

use Symfony\Bundle\SecurityBundle\Security;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

final class GlobalExtension extends AbstractExtension implements GlobalsInterface
{
    /**
     * @param array<string, string> $supportedCountries
     */
    public function __construct(
        private readonly Security $security,
        private readonly string $appName,
        private readonly array $appSocial,
        private readonly string $supportPhone,
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

        // Social media defaults
        $response['social'] = $this->appSocial;

        // Organization data
        $response['organization'] = [
            'name' => $this->appName,
            'phone' => $this->supportPhone,
            'social_links' => $this->appSocial,
        ];

        if (!$user) {
            $countries = [];
            foreach ($this->supportedCountries as $code => $name) {
                $countries[] = ['code' => $code, 'name' => $name];
            }
            $response['countries'] = $countries;
        }

        return $response;
    }
}
