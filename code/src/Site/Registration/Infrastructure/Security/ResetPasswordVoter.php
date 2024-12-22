<?php

declare(strict_types=1);

namespace Site\Registration\Infrastructure\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends Voter<string, mixed>
 */
final class ResetPasswordVoter extends Voter
{
    protected const string RESET_PASSWORD = 'RESET_PASSWORD';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return self::RESET_PASSWORD === $attribute;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        return !($user instanceof UserInterface);
    }
}
