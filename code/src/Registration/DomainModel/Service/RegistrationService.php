<?php

declare(strict_types=1);

namespace App\Registration\DomainModel\Service;

use App\Client\DomainModel\Repository\ClientRepositoryInterface;
use App\Partner\DomainModel\Repository\PartnerRepositoryInterface;
use App\Registration\DomainModel\ValueObject\Email;
use App\Shared\Domain\Enum\Roles;
use Symfony\Component\Security\Core\User\UserInterface;

final readonly class RegistrationService
{
    public function __construct(
        private ClientRepositoryInterface $clientRepository,
        private PartnerRepositoryInterface $partnerRepository
    ) {
    }

    public function isEmailUnique(Email $email): bool
    {
        $clientExists = $this->clientRepository->findByEmail($email->value());
        $partnerExists = $this->partnerRepository->findByEmail($email->value());

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
