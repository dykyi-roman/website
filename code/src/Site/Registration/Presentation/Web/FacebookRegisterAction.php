<?php

declare(strict_types=1);

namespace Site\Registration\Presentation\Web;

use Symfony\Component\Routing\Attribute\Route;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Component\HttpFoundation\RedirectResponse;

class FacebookRegisterAction
{
    #[Route('/connect/facebook', name: 'connect_facebook_start')]
    public function connect(ClientRegistry $clientRegistry): RedirectResponse
    {
        return $clientRegistry->getClient('facebook')->redirect(['email'], []);
    }

    #[Route('/connect/facebook/check', name: 'connect_facebook_check')]
    public function connectCheck(): void
    {
        // Symfony automatic process this route
    }
}