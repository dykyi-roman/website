<?php

declare(strict_types=1);

namespace App\Registration\Application\Command;

use App\Client\DomainModel\Enum\ClientId;
use App\Client\DomainModel\Model\Client;
use App\Partner\DomainModel\Enum\PartnerId;
use App\Partner\DomainModel\Model\Partner;
use App\Registration\DomainModel\Event\UserRegisteredEvent;
use App\Registration\DomainModel\Repository\UserRepositoryInterface;
use App\Registration\DomainModel\Service\AuthenticationService;
use App\Shared\Domain\ValueObject\Email;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[AsMessageHandler]
final readonly class RegisterUserCommandHandler
{
    public function __construct(
        private AuthenticationService $authenticationService,
        private TokenStorageInterface $tokenStorage,
        private UserRepositoryInterface $userRepository,
        private MessageBusInterface $eventBus,
    ) {
    }

    public function __invoke(RegisterUserCommand $command): void
    {
        $this->checkIfEmailAlreadyExists($command->email);

        $user = $this->createUser($command);
        $user->setPassword($this->authenticationService->hashPassword($user, $command->password));

        $this->saveUser($user);

        $this->eventBus->dispatch(
            new UserRegisteredEvent(
                $user->getId()->toRfc4122(),
                $user->getEmail(),
                $user->getName(),
                new \DateTimeImmutable(),
            ),
        );

        $this->loginUserAfterRegistration($user);
    }

    private function saveUser(UserInterface $user): void
    {
        try {
            $this->userRepository->save($user);
        } catch (\Throwable $exception) {
            throw new \DomainException(sprintf('Failed to register user: %s', $exception->getMessage()));
        }
    }

    private function checkIfEmailAlreadyExists(string $email): void
    {
        if (!$this->userRepository->isEmailUnique(Email::fromString($email))) {
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
