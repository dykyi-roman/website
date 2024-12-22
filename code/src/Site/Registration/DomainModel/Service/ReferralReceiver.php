<?php

declare(strict_types=1);

namespace Site\Registration\DomainModel\Service;

use Symfony\Component\HttpFoundation\RequestStack;

final readonly class ReferralReceiver
{
    public function __construct(
        private RequestStack $requestStack,
    ) {
    }

    public function referral(string $name = 'reff'): string
    {
        $request = $this->requestStack->getCurrentRequest();
        if (null === $request) {
            return '';
        }

        return $request->cookies->get($name, $request->query->get($name, ''));
    }
}
