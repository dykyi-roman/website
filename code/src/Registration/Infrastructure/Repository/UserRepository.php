<?php

declare(strict_types=1);

namespace App\Registration\Infrastructure\Repository;

use App\Client\DomainModel\Repository\ClientRepositoryInterface;
use App\Partner\DomainModel\Repository\PartnerRepositoryInterface;
use App\Registration\DomainModel\Repository\UserRepositoryInterface;
use App\Shared\DomainModel\Enum\Roles;
use App\Shared\DomainModel\ValueObject\Email;
use Symfony\Component\Security\Core\User\UserInterface;

final readonly class UserRepository implements UserRepositoryInterface
{
    public function __construct(
        private ClientRepositoryInterface $clientRepository,
        private PartnerRepositoryInterface $partnerRepository,
    ) {
    }

    public function findByEmail(Email $email): ?UserInterface
    {
        $user = $this->clientRepository->findByEmail($email);
        if (!$user) {
            $user = $this->partnerRepository->findByEmail($email);
        }

        return $user;
    }

    public function isEmailUnique(Email $email): bool
    {
        $clientExists = $this->clientRepository->findByEmail($email);
        $partnerExists = $this->partnerRepository->findByEmail($email);

        return !($clientExists || $partnerExists);
    }

    public function save(UserInterface $user): void
    {
        if (in_array(Roles::ROLE_PARTNER->value, $user->getRoles(), true)) {
            $this->partnerRepository->save($user);
        } elseif (in_array(Roles::ROLE_CLIENT->value, $user->getRoles(), true)) {
            $this->clientRepository->save($user);
        } else {
            throw new \InvalidArgumentException('Invalid user type');
        }
    }
}
