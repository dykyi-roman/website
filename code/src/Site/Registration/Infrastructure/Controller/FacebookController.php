<?php

declare(strict_types=1);

namespace Site\Registration\Infrastructure\Controller;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class FacebookController extends AbstractController
{
    #[Route('/connect/facebook', name: 'connect_facebook')]
    public function connectAction(ClientRegistry $clientRegistry): Response
    {
        return $clientRegistry
            ->getClient('facebook')
            ->redirect([
                'public_profile', 'email'
            ]);
    }

    #[Route('/connect/facebook/check', name: 'connect_facebook_check')]
    public function connectCheckAction(): void
    {
        // This method will not be called - the authenticator will handle it
    }
}
