<?php

declare(strict_types=1);

namespace App\Dashboard\Presentation\Web\Twig;

use Symfony\Bundle\SecurityBundle\Security;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

final class GlobalExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(
        private readonly string $appName,
        private readonly array $appSocial,
        private readonly string $supportPhone,
        private readonly Security $security,
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
            $response['countries'] = [
                ['code' => 'ua', 'name' => 'Україна'],
            ];
        }

        $response['global_languages'] = [
            ['code' => 'en', 'name' => 'English'],
            ['code' => 'uk', 'name' => 'Українська'],
        ];

        return $response;
    }
}
