<?php

declare(strict_types=1);

namespace App\Registration\Infrastructure\Security;

use App\Client\DomainModel\Model\Client;
use App\Client\DomainModel\Repository\ClientRepositoryInterface;
use App\Partner\DomainModel\Model\Partner;
use App\Partner\DomainModel\Repository\PartnerRepositoryInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class MultiUserProvider implements UserProviderInterface
{
    public function __construct(
        private readonly ClientRepositoryInterface $clientRepository,
        private readonly PartnerRepositoryInterface $partnerRepository
    ) {
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $user = $this->clientRepository->findByEmail($identifier);
        if (!$user) {
            $user = $this->partnerRepository->findByEmail($identifier);
        }

        if (!$user) {
            throw new UserNotFoundException(sprintf('User with email %s not found', $identifier));
        }

        return $user;
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        // Implement user refresh logic
        return $user;
    }

    public function supportsClass(string $class): bool
    {
        return in_array($class, [Client::class, Partner::class]);
    }
}
