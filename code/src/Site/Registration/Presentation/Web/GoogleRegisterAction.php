<?php

declare(strict_types=1);

namespace Site\Registration\Presentation\Web;

use Symfony\Component\Routing\Attribute\Route;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Component\HttpFoundation\RedirectResponse;

class GoogleRegisterAction
{
    #[Route('/connect/google', name: 'connect_google_start')]
    public function connect(ClientRegistry $clientRegistry): RedirectResponse
    {
        return $clientRegistry->getClient('google')->redirect(['email', 'profile'], []);
    }

    #[Route('/connect/google/check', name: 'connect_google_check')]
    public function connectCheck(): void
    {
        // Symfony automatic process this route
    }
}
