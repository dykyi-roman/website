<?php

declare(strict_types=1);

namespace App\Dashboard\Presentation\Web\Twig;

use Symfony\Bundle\SecurityBundle\Security;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

final class GlobalExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(
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
        $response['social'] = [
            'facebook' => 'https://www.facebook.com/servicehub',
            'twitter' => 'https://twitter.com/servicehub',
            'linkedin' => 'https://www.linkedin.com/company/servicehub',
        ];

        // Organization data
        $response['organization'] = [
            'name' => 'ServiceHub',
            'phone' => '+1-XXX-XXX-XXXX',
            'social_links' => [
                'https://www.facebook.com/servicehub',
                'https://twitter.com/servicehub',
                'https://www.linkedin.com/company/servicehub',
            ],
        ];

        if (!$user) {
            $response['countries'] = [
                ['code' => 'ua', 'name' => 'Ukraine'],
                ['code' => 'es', 'name' => 'Spain'],
            ];
        }

        return $response;
    }
}
