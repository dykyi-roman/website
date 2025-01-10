<?php

declare(strict_types=1);

namespace Site\Registration\Application\UserRegistration\Command;

use Shared\DomainModel\Services\MessageBusInterface;
use Shared\DomainModel\ValueObject\Email;
use Site\Registration\DomainModel\Event\UserRegisteredEvent;
use Site\Registration\DomainModel\Service\ReferralReceiver;
use Site\User\DomainModel\Enum\UserId;
use Site\User\DomainModel\Model\User;
use Site\User\DomainModel\Model\UserInterface;
use Site\User\DomainModel\Repository\UserRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsMessageHandler]
final readonly class RegisterUserCommandHandler
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
        private TokenStorageInterface $tokenStorage,
        private TranslatorInterface $translator,
        private UserRepositoryInterface $userRepository,
        private MessageBusInterface $eventBus,
        private ReferralReceiver $referralReceiver,
    ) {
    }

    public function __invoke(RegisterUserCommand $command): void
    {
        $this->checkIfEmailAlreadyExists($command->email);

        $user = $this->createUser($command);
        $user->updatePassword($this->passwordHasher->hashPassword($user, $command->password));
        $user->withReferral($this->referralReceiver->referral());
        $this->saveUser($user);

        $this->eventBus->dispatch(
            new UserRegisteredEvent(
                $user->id(),
                $user->email(),
                'manual',
            ),
        );

        $this->loginUserAfterRegistration($user);
    }

    private function saveUser(UserInterface $user): void
    {
        try {
            $this->userRepository->save($user);
        } catch (\Throwable $exception) {
            throw new \DomainException(sprintf($this->translator->trans('user_registration_save_error'), $exception->getMessage()));
        }
    }

    private function checkIfEmailAlreadyExists(Email $email): void
    {
        if (!$this->userRepository->isEmailUnique($email)) {
            throw new \DomainException(sprintf($this->translator->trans('user_email_exists_error'), (string) $email));
        }
    }

    private function loginUserAfterRegistration(UserInterface $user): void
    {
        $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
        $this->tokenStorage->setToken($token);
    }

    private function createUser(RegisterUserCommand $command): UserInterface
    {
        return new User(
            new UserId(),
            $command->name,
            $command->email,
            $command->location,
            $command->phone,
            $command->roles,
        );
    }
}
