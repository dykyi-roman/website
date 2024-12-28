<?php

declare(strict_types=1);

namespace Site\User\DomainModel\Service;

use Site\User\DomainModel\Exception\AuthenticationException;
use Site\User\DomainModel\Model\UserInterface;
use Symfony\Bundle\SecurityBundle\Security;

final readonly class UserFetcher
{
    public function __construct(
        private Security $security,
    ) {
    }

    public function logined(): bool
    {
        return null !== $this->security->getToken();
    }

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
