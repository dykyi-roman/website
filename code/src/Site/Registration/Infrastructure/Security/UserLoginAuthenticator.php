<?php

declare(strict_types=1);

namespace Site\Registration\Infrastructure\Security;

use Profile\User\DomainModel\Model\UserInterface;
use Profile\User\DomainModel\Repository\UserRepositoryInterface;
use Shared\DomainModel\Services\MessageBusInterface;
use Shared\DomainModel\ValueObject\Email;
use Site\Registration\DomainModel\Event\UserLoggedInEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;

final class UserLoginAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly MessageBusInterface $eventBus,
    ) {
    }

    public function supports(Request $request): bool
    {
        return '/login' === $request->getPathInfo() && $request->isMethod('POST');
    }

    public function authenticate(Request $request): Passport
    {
        $email = (string) $request->request->get('email', '');
        $password = (string) $request->request->get('password', '');
        $rememberMe = $request->request->getBoolean('remember_me');

        $userBadge = new UserBadge($email, function (string $email) {
            $user = $this->userRepository->findByEmail(Email::fromString($email));
            if (!$user || $user->isDeleted()) {
                throw new AuthenticationException('Invalid username or password');
            }

            return $user;
        });

        return new Passport(
            $userBadge,
            new PasswordCredentials($password),
            $rememberMe ? [new RememberMeBadge()] : []
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return null;
        }

        $this->eventBus->dispatch(
            new UserLoggedInEvent(
                $user->id(),
                $user->email(),
                'manual',
            ),
        );

        return new JsonResponse([
            'success' => true,
            'message' => 'Login successful',
        ]);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        return new JsonResponse([
            'success' => false,
            'errors' => [
                'message' => 'Invalid username or password',
                'field' => 'email',
            ],
        ], Response::HTTP_UNAUTHORIZED);
    }
}
