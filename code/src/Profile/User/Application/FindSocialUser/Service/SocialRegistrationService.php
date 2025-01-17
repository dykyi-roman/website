<?php

namespace Profile\User\Application\FindSocialUser\Service;

use Profile\User\DomainModel\Enum\UserId;
use Profile\User\DomainModel\Model\User;
use Profile\User\DomainModel\Model\UserInterface;
use Profile\User\DomainModel\Repository\UserRepositoryInterface;
use Shared\DomainModel\ValueObject\Email;
use Shared\DomainModel\ValueObject\Location;

final readonly class SocialRegistrationService implements SocialRegistrationServiceInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
    ) {
    }

    public function hasRegistrationByFacebook(Email $email, string $facebookId): ?UserInterface
    {
        $user = $this->userRepository->findByToken('facebookToken', $facebookId);
        if (null === $user) {
            $user = $this->userRepository->findByEmail(Email::fromString($email));
            if (null !== $user) {
                $user->setFacebookToken($facebookId);
                $this->userRepository->save($user);

                return $user;
            }
        }

        return $user;
    }

    public function hasRegistrationByGoogle(Email $email, string $googleId): ?UserInterface
    {
        $user = $this->userRepository->findByToken('googleToken', $googleId);
        if (null === $user) {
            $user = $this->userRepository->findByEmail(Email::fromString($email));
            if (null !== $user) {
                $user->setGoogleToken($googleId);
                $this->userRepository->save($user);

                return $user;
            }
        }

        return $user;
    }

    public function createFacebookUser(
        UserId $userId,
        string $name,
        Email $email,
        Location $location,
        string $token,
        string $referral,
    ): UserInterface {
        $user = new User($userId, $name, $email, $location, null, []);
        $user->setFacebookToken($token);
        $user->withReferral($referral);
        $this->userRepository->save($user);

        return $user;
    }

    public function createGoogleUser(
        UserId $userId,
        string $name,
        Email $email,
        Location $location,
        string $token,
        string $referral,
    ): UserInterface {
        $user = new User($userId, $name, $email, $location, null, []);
        $user->setGoogleToken($token);
        $user->withReferral($referral);
        $this->userRepository->save($user);

        return $user;
    }
}
