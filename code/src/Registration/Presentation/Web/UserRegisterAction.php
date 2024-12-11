<?php

declare(strict_types=1);

namespace App\Registration\Presentation\Web;

use App\Client\DomainModel\Enum\ClientId;
use App\Client\DomainModel\Model\Client;
use App\Client\DomainModel\Repository\ClientRepositoryInterface;
use App\Partner\DomainModel\Enum\PartnerId;
use App\Partner\DomainModel\Model\Partner;
use App\Partner\DomainModel\Repository\PartnerRepositoryInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

final readonly class UserRegisterAction
{
    public function __construct(
        private ClientRepositoryInterface $clientRepository,
        private PartnerRepositoryInterface $partnerRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private LoggerInterface $logger,
    ) {
    }

    #[Route('/register', name: 'app_register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        try {
            $isPartner = isset($data['partner-id']);
            if ($isPartner) {
                $user = new Partner(
                    new PartnerId(),
                    $data['name'],
                    $data['email'],
                    $data['phone'] ?? null,
                    $data['country'] ?? null,
                    $data['city'] ?? null,
                );
            } else {
                $user = new Client(
                    new ClientId(),
                    $data['name'],
                    $data['email'],
                    $data['phone'] ?? null,
                    $data['country'] ?? null,
                    $data['city'] ?? null,
                );
            }

            $user->setPassword($this->passwordHasher->hashPassword($user, $data['password']));
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