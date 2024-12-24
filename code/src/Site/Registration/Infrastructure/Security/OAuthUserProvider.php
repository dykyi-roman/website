<?php

declare(strict_types=1);

namespace Site\Registration\Infrastructure\Security;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Security\User\OAuthUser;
use League\OAuth2\Client\Provider\FacebookUser;
use League\OAuth2\Client\Provider\GoogleUser;
use Shared\DomainModel\ValueObject\Country;
use Shared\DomainModel\ValueObject\Email;
use Shared\DomainModel\ValueObject\Location;
use Site\User\DomainModel\Enum\Roles;
use Site\User\DomainModel\Enum\UserId;
use Site\User\DomainModel\Model\User;
use Site\User\DomainModel\Repository\UserRepositoryInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

final readonly class OAuthUserProvider implements UserProviderInterface
{
    public function __construct(
        private ClientRegistry $clientRegistry,
        private UserRepositoryInterface $userRepository,
    ) {
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $client = $this->clientRegistry->getClient('facebook');
        $accessToken = $client->getAccessToken();

        /** @var FacebookUser $facebookUser */
        $facebookUser = $client->fetchUserFromToken($accessToken);

        return new OAuthUser(
            $facebookUser->getId(),
            [Roles::ROLE_CLIENT->value, Roles::ROLE_PARTNER->value]
        );
    }

    public function loadUserByOAuth2UserGoogle(GoogleUser $oauthUser): UserRepositoryInterface
    {
        $facebookId = $oauthUser->getId();
        $name = $oauthUser->getName() ?? 'Anonymous';
        $email = $oauthUser->getEmail();

        $user = $this->userRepository->findByToken('googleToken', $facebookId);
        if (!$user) {
            $user = new User(
                new UserId(),
                $name,
                Email::fromString($email),
                new Location(
                    new Country('UA'),
                ),
            );
            $user->setFacebookToken($facebookId);
            $this->userRepository->save($user);
        }

        return $user;
    }

    public function loadUserByOAuth2UserFacebook(FacebookUser $oauthUser): UserRepositoryInterface
    {
        $facebookId = $oauthUser->getId();
        $name = $oauthUser->getName() ?? 'Anonymous';
        $email = $oauthUser->getEmail();

        $user = $this->userRepository->findByToken('facebookToken', $facebookId);
        if (!$user) {
            $user = new User(
                new UserId(),
                $name,
                Email::fromString($email),
                new Location(
                    new Country('UA'),
                ),
            );
            $user->setFacebookToken($facebookId);
            $this->userRepository->save($user);
        }

        return $user;
    }

    public function refreshUser(UserInterface $user): UserInterface
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