<?php

declare(strict_types=1);

namespace App\Registration\Presentation\Web;

use Symfony\Component\Routing\Annotation\Route;

final readonly class UserLogoutAction
{
    #[Route('/logout', name: 'app_logout', methods: ['GET'])]
    public function logout(): void
    {
        // Controller can be empty - it will be intercepted by the logout key on your firewall
    }
}
