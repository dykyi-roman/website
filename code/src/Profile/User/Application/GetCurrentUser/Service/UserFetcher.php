<?php

declare(strict_types=1);

namespace Profile\User\Application\GetCurrentUser\Service;

use Profile\User\DomainModel\Exception\AuthenticationException;
use Profile\User\DomainModel\Model\UserInterface;
use Symfony\Bundle\SecurityBundle\Security;

final readonly class UserFetcher implements UserFetcherInterface
{
    public function __construct(
        private Security $security,
    ) {
    }

    public function isLogin(): bool
    {
        return null !== $this->security->getToken();
    }

    /**
     * @throw AuthenticationException
     */
    public function fetch(): UserInterface
    {
        $token = $this->security->getToken();
        if (null === $token) {
            throw AuthenticationException::userNotFound();
        }

        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            throw AuthenticationException::userNotFound();
        }

        return $user;
    }
}
