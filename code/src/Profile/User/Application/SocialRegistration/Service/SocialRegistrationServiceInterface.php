<?php

namespace Profile\User\Application\SocialRegistration\Service;

use Profile\User\DomainModel\Enum\UserId;
use Profile\User\DomainModel\Model\UserInterface;
use Shared\DomainModel\ValueObject\Email;
use Shared\DomainModel\ValueObject\Location;

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
