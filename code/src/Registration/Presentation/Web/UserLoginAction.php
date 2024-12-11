<?php

declare(strict_types=1);

namespace App\Registration\Presentation\Web;

use App\Client\DomainModel\Repository\ClientRepositoryInterface;
use App\Partner\DomainModel\Repository\PartnerRepositoryInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Twig\Environment as TwigEnvironment;

final readonly class UserLoginAction
{
    public function __construct(
        private ClientRepositoryInterface $clientRepository,
        private PartnerRepositoryInterface $partnerRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private LoggerInterface $logger,
        private UrlGeneratorInterface $urlGenerator,
        private TwigEnvironment $twig,
    ) {
    }

    #[Route('/login', name: 'app_login', methods: ['GET', 'POST'])]
    public function login(Request $request, AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        // If this is a POST request, attempt to authenticate
        if (!$request->isMethod('POST')) {
            // For GET requests, render the login page
            return new Response(
                $this->twig->render('@Dashboard/popup/login-popup.html.twig', [
                    'last_username' => $lastUsername,
                    'error' => $error,
                ])
            );
        }

        $credentials = json_decode($request->getContent(), true);
        $email = $credentials['email'] ?? '';
        $password = $credentials['password'] ?? '';

        try {
            $user = $this->clientRepository->findByEmail($email);
            if (!$user) {
                $user = $this->partnerRepository->findByEmail($email);
            }

            // If no user found, return error
            if (!$user) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'User not found'
                ], Response::HTTP_UNAUTHORIZED);
            }

            // Verify password
            $isValidPassword = $this->passwordHasher->isPasswordValid($user, $password);
            if (!$isValidPassword) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Invalid credentials'
                ], Response::HTTP_UNAUTHORIZED);
            }

            // If password is valid, return success with dashboard URL
            return new JsonResponse([
                'success' => true,
                'message' => 'Login successful',
                'redirect' => $this->urlGenerator->generate('dashboard_page')
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Login error: ' . $e->getMessage());

            return new JsonResponse([
                'success' => false,
                'message' => 'An unexpected error occurred'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
