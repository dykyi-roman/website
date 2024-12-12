<?php

declare(strict_types=1);

namespace App\Registration\Application\Command;

use App\Client\DomainModel\Enum\ClientId;
use App\Client\DomainModel\Model\Client;
use App\Client\DomainModel\Repository\ClientRepositoryInterface;
use App\Partner\DomainModel\Enum\PartnerId;
use App\Partner\DomainModel\Model\Partner;
use App\Partner\DomainModel\Repository\PartnerRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserInterface;

#[AsMessageHandler]
final readonly class RegisterUserCommandHandler
{
    public function __construct(
        private ClientRepositoryInterface $clientRepository,
        private PartnerRepositoryInterface $partnerRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private TokenStorageInterface $tokenStorage,
    ) {
    }

    public function __invoke(RegisterUserCommand $command): void
    {
        $this->checkIfEmailAlreadyExists($command->email);

        $user = $this->createUser($command);
        $user->setPassword($this->passwordHasher->hashPassword($user, $command->password));

        if ($command->isPartner) {
            $this->partnerRepository->save($user);
        } else {
            $this->clientRepository->save($user);
        }

        $this->loginUserAfterRegistration($user);
    }

    private function checkIfEmailAlreadyExists(string $email): void
    {
        $clientExists = $this->clientRepository->findByEmail($email);
        $partnerExists = $this->partnerRepository->findByEmail($email);

        if ($clientExists || $partnerExists) {
            throw new \DomainException('Email already exists');
        }
    }

    private function loginUserAfterRegistration(UserInterface $user): void
    {
        $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
        $this->tokenStorage->setToken($token);
    }

    private function createUser(RegisterUserCommand $command): Client|Partner
    {
        return $command->isPartner
            ? new Partner(
                new PartnerId(),
                $command->name,
                $command->email,
                $command->phone,
                $command->country,
                $command->city,
            )
            : new Client(
                new ClientId(),
                $command->name,
                $command->email,
                $command->phone,
                $command->country,
                $command->city,
            );
    }
}
