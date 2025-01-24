<?php

declare(strict_types=1);

namespace Site\Registration\Infrastructure\Security;

use KnpU\OAuth2ClientBundle\Security\User\OAuthUser;
use League\OAuth2\Client\Provider\FacebookUser;
use League\OAuth2\Client\Provider\GoogleUser;
use Profile\User\Application\UserRegistration\Service\SocialRegistrationServiceInterface;
use Profile\User\DomainModel\Enum\Roles;
use Profile\User\DomainModel\Model\UserInterface;
use Shared\DomainModel\Services\MessageBusInterface;
use Shared\DomainModel\ValueObject\Country;
use Shared\DomainModel\ValueObject\Email;
use Shared\DomainModel\ValueObject\Location;
use Shared\DomainModel\ValueObject\UserId;
use Site\Registration\DomainModel\Event\UserRegisteredEvent;
use Site\Registration\DomainModel\Service\CountryDetectorInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface as SymfonyUserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * @implements UserProviderInterface<OAuthUser>
 */
final readonly class OAuthUserProvider implements UserProviderInterface
{
    public function __construct(
        private SocialRegistrationServiceInterface $socialRegistrationService,
        private CountryDetectorInterface $countryDetector,
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

        $user = $this->socialRegistrationService->hasRegistrationByGoogle(Email::fromString($email), $googleId);
        if (null !== $user) {
            return $user;
        }

        if ($country = $this->countryDetector->detect()) {
            $country = new Country($country->code);
        }
        $user = $this->socialRegistrationService->createGoogleUser(
            new UserId(),
            $name,
            Email::fromString($email),
            new Location($country),
            $googleId,
        );

        $this->eventBus->dispatch(
            new UserRegisteredEvent(
                $user->id(),
                $user->email(),
                'google',
            ),
        );

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

        $user = $this->socialRegistrationService->hasRegistrationByFacebook(Email::fromString($email), $facebookId);
        if (null !== $user) {
            return $user;
        }

        if ($country = $this->countryDetector->detect()) {
            $country = new Country($country->code);
        }

        $user = $this->socialRegistrationService->createFacebookUser(
            new UserId(),
            $name,
            Email::fromString($email),
            new Location($country),
            $facebookId,
        );

        $this->eventBus->dispatch(
            new UserRegisteredEvent(
                $user->id(),
                $user->email(),
                'facebook',
            ),
        );

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
