<?php

declare(strict_types=1);

namespace Site\Registration\Presentation\Web;

use Symfony\Component\Routing\Attribute\Route;

final readonly class UserLogoutAction
{
    #[Route('/logout', name: 'logout', methods: ['GET'])]
    public function logout(): void
    {
        // Controller can be empty - it will be intercepted by the logout key on your firewall
    }
}
