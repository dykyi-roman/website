<?php

declare(strict_types=1);

namespace Site\Registration\Infrastructure\Security;

use KnpU\OAuth2ClientBundle\Security\User\OAuthUser;
use League\OAuth2\Client\Provider\FacebookUser;
use League\OAuth2\Client\Provider\GoogleUser;
use Profile\User\DomainModel\Enum\Roles;
use Profile\User\DomainModel\Enum\UserId;
use Profile\User\DomainModel\Model\User;
use Profile\User\DomainModel\Model\UserInterface;
use Profile\User\DomainModel\Repository\UserRepositoryInterface;
use Shared\DomainModel\Services\MessageBusInterface;
use Shared\DomainModel\ValueObject\Country;
use Shared\DomainModel\ValueObject\Email;
use Shared\DomainModel\ValueObject\Location;
use Site\Registration\DomainModel\Event\UserRegisteredEvent;
use Site\Registration\DomainModel\Service\CountryDetectorInterface;
use Site\Registration\DomainModel\Service\ReferralReceiver;
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
        private ReferralReceiver $referralReceiver,
        private MessageBusInterface $eventBus,
    ) {
    }

    public function loadUserByIdentifier(string $identifier): SymfonyUserInterface
    {
        return new OAuthUser($identifier, [Roles::ROLE_CLIENT, Roles::ROLE_PARTNER]);
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
                null,
                []
            );
            $user->setGoogleToken($googleId);
            $user->withReferral($this->referralReceiver->referral());
            $this->userRepository->save($user);

            $this->eventBus->dispatch(
                new UserRegisteredEvent(
                    $user->id(),
                    $user->email(),
                    'google',
                ),
            );
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
                null,
                []
            );
            $user->setFacebookToken($facebookId);
            $user->withReferral($this->referralReceiver->referral());
            $this->userRepository->save($user);

            $this->eventBus->dispatch(
                new UserRegisteredEvent(
                    $user->id(),
                    $user->email(),
                    'facebook',
                ),
            );
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
