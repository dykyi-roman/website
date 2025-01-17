<?php

declare(strict_types=1);

namespace Site\Registration\Application\UserRegistration\Command;

use Profile\User\Application\ManualRegistration\Service\ManualRegistrationServiceInterface;
use Profile\User\DomainModel\Model\UserInterface;
use Profile\User\DomainModel\Repository\UserRepositoryInterface;
use Shared\DomainModel\Services\MessageBusInterface;
use Shared\DomainModel\ValueObject\Email;
use Site\Registration\DomainModel\Event\UserRegisteredEvent;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsMessageHandler]
final readonly class RegisterUserCommandHandler
{
    public function __construct(
        private TokenStorageInterface $tokenStorage,
        private TranslatorInterface $translator,
        private UserRepositoryInterface $userRepository,
        private MessageBusInterface $eventBus,
        private ManualRegistrationServiceInterface $manualRegistrationService,
    ) {
    }

    public function __invoke(RegisterUserCommand $command): void
    {
        $this->checkIfEmailAlreadyExists($command->email);

        $user = $this->manualRegistrationService->createUser(
            $command->name,
            $command->email,
            $command->location,
            $command->phone,
            $command->password,
        );

        $this->eventBus->dispatch(
            new UserRegisteredEvent(
                $user->id(),
                $user->email(),
                'manual',
            ),
        );

        $this->loginUserAfterRegistration($user);
    }

    private function checkIfEmailAlreadyExists(Email $email): void
    {
        if (null !== $this->userRepository->findByEmail($email)) {
            throw new \DomainException(sprintf($this->translator->trans('user_email_exists_error'), (string) $email));
        }
    }

    private function loginUserAfterRegistration(UserInterface $user): void
    {
        $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
        $this->tokenStorage->setToken($token);
    }
}
