<?php

declare(strict_types=1);

namespace App\Partner\DomainModel\Model;

use Symfony\Component\Security\Core\User\UserInterface;

class Partner implements UserInterface
{

    public function getRoles(): array
    {
        // TODO: Implement getRoles() method.
    }

    public function eraseCredentials(): void
    {
        // TODO: Implement eraseCredentials() method.
    }

    public function getUserIdentifier(): string
    {
        // TODO: Implement getUserIdentifier() method.
    }
}