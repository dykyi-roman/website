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
        $countries = [
            ['code' => 'ua', 'name' => 'Ukraine'],
            ['code' => 'es', 'name' => 'Spain'],
        ];

        if (!$user) {
            $response['countries'] = $countries;
        }

        return $response;
    }
}
