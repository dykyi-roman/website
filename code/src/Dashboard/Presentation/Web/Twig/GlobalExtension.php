<?php

declare(strict_types=1);

namespace App\Dashboard\Presentation\Web\Twig;

use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

final class GlobalExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(
        private readonly string $appName,
        private readonly array $appSocial,
        private readonly string $supportPhone,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public
    function getGlobals(): array
    {
        $response = [];
        // Social media defaults
        $response['social'] = $this->appSocial;

        // Organization data
        $response['organization'] = [
            'name' => $this->appName,
            'phone' => $this->supportPhone,
            'social_links' => $this->appSocial,
        ];

        return $response;
    }
}
