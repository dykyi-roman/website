<?php

declare(strict_types=1);

namespace App\Registration\Presentation\Web;

use App\Client\DomainModel\Model\Client;
use App\Client\DomainModel\Repository\ClientRepositoryInterface;
use App\Partner\DomainModel\Model\Partner;
use App\Partner\DomainModel\Repository\PartnerRepositoryInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Uid\Uuid;
use Twig\Environment as TwigEnvironment;

final readonly class SecurityController
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
        if ($request->isMethod('POST')) {
            $credentials = json_decode($request->getContent(), true);
            $email = $credentials['email'] ?? '';
            $password = $credentials['password'] ?? '';

            try {
                // First, try to find user in ClientRepositoryInterface
                $user = $this->clientRepository->findByEmail($email);
                
                // If not found in client, try PartnerRepositoryInterface
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
                    'redirect' => $this->urlGenerator->generate('app_dashboard')
                ]);

            } catch (\Exception $e) {
                $this->logger->error('Login error: ' . $e->getMessage());
                return new JsonResponse([
                    'success' => false,
                    'message' => 'An unexpected error occurred'
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        // For GET requests, render the login page
        return new Response($this->twig->render('@Dashboard/popup/login-popup.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]));
    }

    #[Route('/logout', name: 'app_logout', methods: ['GET'])]
    public function logout(): void
    {
        // Controller can be empty - it will be intercepted by the logout key on your firewall
    }

    #[Route('/register', name: 'app_register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $isPartner = isset($data['partner-id']);
        if ($isPartner) {
            $user = new Partner();
            $user->setRoles(['ROLE_PARTNER']);
        } else {
            $user = new Client();
            $user->setRoles(['ROLE_CLIENT']);
        }

        try {
            $user->setId(Uuid::v4());
            $user->setEmail($data['email']);
            $user->setName($data['name']);
            $user->setPassword($this->passwordHasher->hashPassword($user, $data['password']));
            $user->setPhone($data['phone'] ?? null);
            $user->setCountry($data['country'] ?? null);
            $user->setCity($data['city'] ?? null);

            $isPartner ? $this->partnerRepository->save($user) : $this->clientRepository->save($user);

            return new JsonResponse([
                'success' => true,
                'message' => 'Registration successful',
                'user_type' => $isPartner ? 'partner' : 'client'
            ], Response::HTTP_CREATED);

        } catch (\Throwable $exception) {
            $this->logger->error($exception->getMessage());

            return new JsonResponse([
                'success' => false,
                'message' => 'Registration failed',
                'errors' => [
                    'message' => 'An error occurred during registration. Please try again.'
                ]
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
