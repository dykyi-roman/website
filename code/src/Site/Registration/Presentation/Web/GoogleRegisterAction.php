<?php

declare(strict_types=1);

namespace Site\Registration\Presentation\Web;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;

class GoogleRegisterAction
{
    #[Route('/connect/google', name: 'connect_google_start', methods: ['GET', 'POST'])]
    public function connect(ClientRegistry $clientRegistry): RedirectResponse
    {
        return $clientRegistry->getClient('google')->redirect(['email', 'profile'], []);
    }

    #[Route('/connect/google/check', name: 'connect_google_check', methods: ['GET', 'POST'])]
    public function connectCheck(): void
    {
        // Symfony automatic process this route
    }
}
