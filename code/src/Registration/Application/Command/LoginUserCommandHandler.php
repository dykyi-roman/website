<?php

declare(strict_types=1);

namespace App\Registration\Application\Command;

use App\Registration\DomainModel\Event\UserLoggedInEvent;
use App\Registration\DomainModel\Exception\InvalidCredentialsException;
use App\Registration\DomainModel\Repository\UserRepositoryInterface;
use App\Registration\DomainModel\Service\AuthenticationService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserInterface;

#[AsMessageHandler]
final readonly class LoginUserCommandHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private AuthenticationService $authenticationService,
        private TokenStorageInterface $tokenStorage,
        private MessageBusInterface $eventBus,
    ) {
    }

    /**
     * @throws InvalidCredentialsException
     */
    public function __invoke(LoginUserCommand $command): void
    {
        $user = $this->userRepository->findByEmail($command->email);
        if (!$user || !$this->authenticationService->verifyPassword($user, $command->password)) {
            throw new InvalidCredentialsException('Invalid username or password');
        }

        $this->loginUserAfterRegistration($user);

        $this->eventBus->dispatch(
            new UserLoggedInEvent(
                $user->getId(),
                $user->getEmail(),
                new \DateTimeImmutable(),
            ),
        );
    }

    private function loginUserAfterRegistration(UserInterface $user): void
    {
        $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
        $this->tokenStorage->setToken($token);
    }
}
