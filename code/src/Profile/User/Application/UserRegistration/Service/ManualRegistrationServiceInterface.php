<?php

declare(strict_types=1);

namespace Profile\User\Application\UserRegistration\Service;

use Profile\User\DomainModel\Model\UserInterface;
use Shared\DomainModel\ValueObject\Email;
use Shared\DomainModel\ValueObject\Location;

interface ManualRegistrationServiceInterface
{
    public function createUser(
        string $name,
        Email $email,
        Location $location,
        string $phone,
        string $password,
    ): UserInterface;
}
