<?php

declare(strict_types=1);

namespace Site\User\Presentation\Api;

use Shared\DomainModel\Services\MessageBusInterface;
use Site\User\Application\UpdateUserSettings\Command\ChangeUserCommand;
use Site\User\DomainModel\Exception\AuthenticationException;
use Site\User\DomainModel\Exception\UserNotFoundException;
use Site\User\DomainModel\Service\UserFetcher;
use Site\User\Presentation\Api\Request\ChangeUserRequestDto;
use Site\User\Presentation\Api\Response\ChangeUserJsonResponder;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/v1/users', name: 'api_users_change', methods: ['PUT'])]
final readonly class ChangeUserAction
{
    public function __invoke(
        #[MapRequestPayload] ChangeUserRequestDto $request,
        UserFetcher $userFetcher,
        MessageBusInterface $messageBus,
        ChangeUserJsonResponder $responder,
    ): ChangeUserJsonResponder {
        try {
            $messageBus->dispatch(
                new ChangeUserCommand(
                    userId: $userFetcher->fetch()->id(),
                    name: $request->name,
                    email: $request->email,
                    phone: $request->phone,
                    avatar: $request->avatar,
                )
            );

            return $responder->success('Ok')->respond();
        } catch (AuthenticationException) {
            return $responder->error('User not authenticated')->respond();
        } catch (UserNotFoundException) {
            return $responder->error('User not found error')->respond();
        } catch (\InvalidArgumentException) {
            return $responder->error('Invalid argument error')->respond();
        }
    }
}