<?php

declare(strict_types=1);

namespace App\Registration\Presentation\Web;

use App\Client\DomainModel\Enum\ClientId;
use App\Client\DomainModel\Model\Client;
use App\Client\DomainModel\Repository\ClientRepositoryInterface;
use App\Partner\DomainModel\Enum\PartnerId;
use App\Partner\DomainModel\Model\Partner;
use App\Partner\DomainModel\Repository\PartnerRepositoryInterface;
use App\Registration\Presentation\Web\Request\UserRegisterRequestDTO;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

final readonly class UserRegisterAction
{
    public function __construct(
        private ClientRepositoryInterface $clientRepository,
        private PartnerRepositoryInterface $partnerRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private TokenStorageInterface $tokenStorage,
        private LoggerInterface $logger,
    ) {
    }

    #[Route('/register', name: 'register', methods: ['POST'])]
    public function register(
        #[MapRequestPayload] UserRegisterRequestDTO $request,
    ): JsonResponse {
        try {
            // Check if email already exists in both repositories
            $clientExists = $this->clientRepository->findByEmail($request->email);
            $partnerExists = $this->partnerRepository->findByEmail($request->email);

            if ($clientExists || $partnerExists) {
                return new JsonResponse([
                    'success' => false,
                    'errors' => [
                        'message' => 'Email already exists',
                        'field' => 'email'
                    ]
                ], Response::HTTP_BAD_REQUEST);
            }

            if ($request->isPartner()) {
                $user = new Partner(
                    new PartnerId(),
                    $request->name,
                    $request->email,
                    $request->phone,
                    $request->country,
                    $request->city,
                );
            } else {
                $user = new Client(
                    new ClientId(),
                    $request->name,
                    $request->email,
                    $request->phone,
                    $request->country,
                    $request->city,
                );
            }

            $user->setPassword($this->passwordHasher->hashPassword($user, $request->password));
            $request->isPartner() ? $this->partnerRepository->save($user) : $this->clientRepository->save($user);

            // Or - Login the user after registration using the form login authenticator
            $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
            $this->tokenStorage->setToken($token);

            return new JsonResponse([
                'success' => true,
                'message' => 'Registration successful',
            ], Response::HTTP_CREATED);
        } catch (\Throwable $exception) {
            $this->logger->error($exception->getMessage());

            return new JsonResponse([
                'success' => false,
                'errors' => [
                    'message' => 'An error occurred during registration. Please try again.'
                ]
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
