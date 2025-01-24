<?php

namespace Profile\User\Application\UserRegistration\Service;

use Profile\User\DomainModel\Model\UserInterface;
use Shared\DomainModel\ValueObject\Email;
use Shared\DomainModel\ValueObject\Location;
use Shared\DomainModel\ValueObject\UserId;

interface SocialRegistrationServiceInterface
{
    public function hasRegistrationByFacebook(Email $email, string $facebookId): ?UserInterface;

    public function hasRegistrationByGoogle(Email $email, string $googleId): ?UserInterface;

    public function createFacebookUser(
        UserId $userId,
        string $name,
        Email $email,
        Location $location,
        string $token,
    ): UserInterface;

    public function createGoogleUser(
        UserId $userId,
        string $name,
        Email $email,
        Location $location,
        string $token,
    ): UserInterface;
}
