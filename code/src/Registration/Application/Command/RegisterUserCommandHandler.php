<?php

declare(strict_types=1);

namespace App\Registration\Application\Command;

use App\Client\DomainModel\Enum\ClientId;
use App\Client\DomainModel\Model\Client;
use App\Partner\DomainModel\Enum\PartnerId;
use App\Partner\DomainModel\Model\Partner;
use App\Registration\DomainModel\Service\RegistrationService;
use App\Registration\DomainModel\ValueObject\Email;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[AsMessageHandler]
final readonly class RegisterUserCommandHandler
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
        private TokenStorageInterface $tokenStorage,
        private RegistrationService $registrationService,
    ) {
    }

    public function __invoke(RegisterUserCommand $command): void
    {
        $this->checkIfEmailAlreadyExists($command->email);

        $user = $this->createUser($command);
        $user->setPassword($this->passwordHasher->hashPassword($user, $command->password));

        $this->saveUser($user);

        $this->loginUserAfterRegistration($user);
    }

    private function saveUser(UserInterface $user): void
    {
        try {
            $this->registrationService->save($user);
        } catch (\Throwable $exception) {
            throw new \DomainException(sprintf('Failed to register user: %s', $exception->getMessage()));
        }
    }

    private function checkIfEmailAlreadyExists(string $email): void
    {
        if (!$this->registrationService->isEmailUnique(Email::fromString($email))) {
            throw new \DomainException(sprintf('Email "%s" already exists', $email));
        }
    }

    private function loginUserAfterRegistration(UserInterface $user): void
    {
        $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
        $this->tokenStorage->setToken($token);
    }

    private function createUser(RegisterUserCommand $command): UserInterface|PasswordAuthenticatedUserInterface
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
