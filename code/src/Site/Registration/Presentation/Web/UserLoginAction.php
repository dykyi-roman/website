<?php

declare(strict_types=1);

namespace Site\Registration\Presentation\Web;

use Symfony\Component\Routing\Attribute\Route;

final readonly class UserLoginAction
{
    #[Route('/login', name: 'login', methods: ['POST'])]
    public function login(): void
    {
        // Controller can be empty - it will be intercepted by the logout key on your firewall
    }
}
