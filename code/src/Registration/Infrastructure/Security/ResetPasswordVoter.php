<?php

declare(strict_types=1);

namespace App\Registration\Infrastructure\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

final class ResetPasswordVoter extends Voter
{
    protected const string RESET_PASSWORD = 'RESET_PASSWORD';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === self::RESET_PASSWORD;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        return !($user instanceof UserInterface);
    }
}
