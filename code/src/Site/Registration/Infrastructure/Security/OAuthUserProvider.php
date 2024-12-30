<?php

declare(strict_types=1);

namespace Site\Registration\Infrastructure\Security;

use KnpU\OAuth2ClientBundle\Security\User\OAuthUser;
use League\OAuth2\Client\Provider\FacebookUser;
use League\OAuth2\Client\Provider\GoogleUser;
use Shared\DomainModel\ValueObject\Country;
use Shared\DomainModel\ValueObject\Email;
use Shared\DomainModel\ValueObject\Location;
use Site\Registration\DomainModel\Service\CountryDetectorInterface;
use Site\User\DomainModel\Enum\UserId;
use Site\User\DomainModel\Model\User;
use Site\User\DomainModel\Model\UserInterface;
use Site\User\DomainModel\Repository\UserRepositoryInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface as SymfonyUserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * @implements UserProviderInterface<OAuthUser>
 */
final readonly class OAuthUserProvider implements UserProviderInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private CountryDetectorInterface $countryDetector,
    ) {
    }

    public function loadUserByIdentifier(string $identifier): SymfonyUserInterface
    {
        return $this->userRepository->findByEmail(Email::fromString($identifier));
    }

    public function loadUserByOAuth2UserGoogle(GoogleUser $oauthUser): UserInterface
    {
        $googleId = $oauthUser->getId();
        if (!is_string($googleId)) {
            throw new \RuntimeException('Google user ID must be a string');
        }

        $name = $oauthUser->getName() ?: 'Anonymous';
        $email = $oauthUser->getEmail();
        if (null === $email) {
            throw new \RuntimeException('Email is required for registration');
        }

        $user = $this->userRepository->findByToken('googleToken', $googleId);
        if (null === $user) {
            $user = $this->userRepository->findByEmail(Email::fromString($email));
            if (null !== $user) {
                $user->setGoogleToken($googleId);
                $this->userRepository->save($user);

                return $user;
            }
        }

        if (null === $user) {
            if ($country = $this->countryDetector->detect()) {
                $country = new Country($country->code);
            }
            $user = new User(
                new UserId(),
                $name,
                Email::fromString($email),
                new Location($country),
            );
            $user->setGoogleToken($googleId);
            $this->userRepository->save($user);
        }

        return $user;
    }

    public function loadUserByOAuth2UserFacebook(FacebookUser $oauthUser): UserInterface
    {
        $facebookId = $oauthUser->getId();
        if (!is_string($facebookId)) {
            throw new \RuntimeException('Facebook user ID must be a string');
        }

        $name = $oauthUser->getName() ?: 'Anonymous';
        $email = $oauthUser->getEmail();
        if (null === $email) {
            throw new \RuntimeException('Email is required for registration');
        }

        $user = $this->userRepository->findByToken('googleToken', $facebookId);
        if (null === $user) {
            $user = $this->userRepository->findByEmail(Email::fromString($email));
            if (null !== $user) {
                $user->setFacebookToken($facebookId);
                $this->userRepository->save($user);

                return $user;
            }
        }

        if (null === $user) {
            if ($country = $this->countryDetector->detect()) {
                $country = new Country($country->code);
            }
            $user = new User(
                new UserId(),
                $name,
                Email::fromString($email),
                new Location($country),
            );
            $user->setFacebookToken($facebookId);
            $this->userRepository->save($user);
        }

        return $user;
    }

    public function refreshUser(SymfonyUserInterface $user): SymfonyUserInterface
    {
        if (!$user instanceof OAuthUser) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

            return $this->loadUserByIdentifier($user->getUserIdentifier());
    }

    public function supportsClass(string $class): bool
    {
        return OAuthUser::class === $class;
    }
}
