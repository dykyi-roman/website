<?php

declare(strict_types=1);

namespace Site\Registration\Presentation\Web;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;

class FacebookRegisterAction
{
    #[Route('/connect/facebook', name: 'connect_facebook_start', methods: ['GET', 'POST'])]
    public function connect(ClientRegistry $clientRegistry): RedirectResponse
    {
        return $clientRegistry->getClient('facebook')->redirect(['email'], []);
    }

    #[Route('/connect/facebook/check', name: 'connect_facebook_check', methods: ['GET', 'POST'])]
    public function connectCheck(): void
    {
        // Symfony automatic process this route
    }
}
