<?php

declare(strict_types=1);

namespace App\Registration\Presentation\Web;

use App\Client\DomainModel\Model\Client;
use App\Client\DomainModel\Repository\ClientRepositoryInterface;
use App\Partner\DomainModel\Model\Partner;
use App\Partner\DomainModel\Repository\PartnerRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Uid\Uuid;

final class SecurityController extends AbstractController
{
    public function __construct(
        private readonly ClientRepositoryInterface $clientRepository,
        private readonly PartnerRepositoryInterface $partnerRepository,
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {
    }

    #[Route('/login', name: 'app_login', methods: ['GET', 'POST'])]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_dashboard');
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('@Dashboard/popup/login-popup.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
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

            if ($isPartner) {
                $this->clientRepository->save($user);
            } else {
                $this->partnerRepository->save($user);
            }

            return new JsonResponse([
                'success' => true,
                'message' => 'Registration successful',
                'user_type' => $isPartner ? 'partner' : 'client'
            ], Response::HTTP_CREATED);

        } catch (\Throwable) {
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
