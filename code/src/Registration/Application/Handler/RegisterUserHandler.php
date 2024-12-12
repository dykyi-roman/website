<?php

declare(strict_types=1);

namespace App\Registration\Application\Handler;

use App\Client\DomainModel\Enum\ClientId;
use App\Client\DomainModel\Model\Client;
use App\Client\DomainModel\Repository\ClientRepositoryInterface;
use App\Partner\DomainModel\Enum\PartnerId;
use App\Partner\DomainModel\Model\Partner;
use App\Partner\DomainModel\Repository\PartnerRepositoryInterface;
use App\Registration\Application\Command\RegisterUserCommand;
use App\Registration\DomainModel\Event\UserRegistered;
use App\Registration\DomainModel\Service\RegistrationService;
use App\Registration\DomainModel\ValueObject\Email;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * @see RegisterUserCommand
 */
final readonly class RegisterUserHandler
{
    public function __construct(
        private RegistrationService $registrationService,
        private ClientRepositoryInterface $clientRepository,
        private PartnerRepositoryInterface $partnerRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private TokenStorageInterface $tokenStorage,
        private EventDispatcherInterface $eventDispatcher
    ) {
    }

    public function handle(RegisterUserCommand $command): void
    {
        $email = Email::fromString($command->email);

        if (!$this->registrationService->isEmailUnique($email)) {
            throw new \DomainException('Email already exists');
        }

        $user = $this->createUser($command);
        $user->setPassword($this->passwordHasher->hashPassword($user, $command->password));

        $command->isPartner ? $this->partnerRepository->save($user) : $this->clientRepository->save($user);

        $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
        $this->tokenStorage->setToken($token);

        $this->eventDispatcher->dispatch(
            new UserRegistered(
                $user->getId()->toRfc4122(),
                $email->value(),
                $command->isPartner ? 'partner' : 'client',
                new \DateTimeImmutable()
            ),
        );
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
