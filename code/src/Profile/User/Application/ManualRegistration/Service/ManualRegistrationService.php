<?php

declare(strict_types=1);

namespace Profile\User\Application\ManualRegistration\Service;

use Profile\User\DomainModel\Enum\UserId;
use Profile\User\DomainModel\Model\User;
use Profile\User\DomainModel\Model\UserInterface;
use Profile\User\DomainModel\Repository\UserRepositoryInterface;
use Shared\DomainModel\ValueObject\Email;
use Shared\DomainModel\ValueObject\Location;
use Site\Registration\DomainModel\Service\ReferralReceiverInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final readonly class ManualRegistrationService implements ManualRegistrationServiceInterface
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
        private ReferralReceiverInterface $referralReceiver,
        private UserRepositoryInterface $userRepository,
    ) {
    }

    public function createUser(
        string $name,
        Email $email,
        Location $location,
        ?string $phone,
        string $password,
    ): UserInterface {
        $user = new User(
            new UserId(),
            $name,
            $email,
            $location,
            $phone,
        );

        $user->withReferral($this->referralReceiver->referral());
        $user->updatePassword($this->passwordHasher->hashPassword($user, $password));
        $this->userRepository->save($user);

        return $user;
    }
}
