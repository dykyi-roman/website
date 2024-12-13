<?php

declare(strict_types=1);

namespace App\Registration\Application\Command;

use App\Registration\DomainModel\Event\UserLoggedInEvent;
use App\Registration\DomainModel\Exception\InvalidCredentialsException;
use App\Registration\DomainModel\Repository\UserRepositoryInterface;
use App\Registration\DomainModel\Service\AuthenticationService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
final readonly class LoginUserCommandHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private AuthenticationService $authenticationService,
        private MessageBusInterface $eventBus,
    ) {
    }

    public function __invoke(LoginUserCommand $command): void
    {
        $user = $this->userRepository->findByEmail($command->email);
        if (!$user || !$this->authenticationService->verifyPassword($user, $command->password)) {
            throw new InvalidCredentialsException('Invalid username or password');
        }

        $this->eventBus->dispatch(
            new UserLoggedInEvent(
                $user->getId()->toRfc4122(),
                $user->getEmail(),
                new \DateTimeImmutable(),
            ),
        );
    }
}
