<?php

declare(strict_types=1);

namespace Profile\User\Presentation\Api;

use OpenApi\Attributes as OA;
use Profile\User\Application\UserAuthentication\Command\CreateUserPasswordCommand;
use Profile\User\Application\UserAuthentication\Service\UserFetcherInterface;
use Profile\User\Application\UserManagement\Command\ChangeUserPasswordCommand;
use Profile\User\Presentation\Api\Request\ChangePasswordRequestDto;
use Profile\User\Presentation\Api\Request\CreatePasswordRequestDto;
use Profile\User\Presentation\Api\Response\ChangePasswordJsonResponder;
use Profile\User\Presentation\Api\Response\CreatePasswordJsonResponder;
use Shared\DomainModel\Services\MessageBusInterface;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class CreatePasswordAction
{
    #[Route('/v1/users/self/password', name: 'create_user_password', methods: ['POST'])]
    #[OA\Post(
        path: '/api/v1/users/self/password',
        description: 'Create the password for the authenticated user',
        summary: 'Create user password',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['password', 'confirmationPassword'],
                properties: [
                    new OA\Property(property: 'password', type: 'string', example: 'current123'),
                    new OA\Property(property: 'confirmationPassword', type: 'string', example: 'new123'),
                ]
            )
        ),
        tags: ['Settings'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Password created successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Ok'),
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Validation error',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'Password created failed'),
                    ]
                )
            ),
        ]
    )]
    public function __invoke(
        #[MapRequestPayload] CreatePasswordRequestDto $request,
        MessageBusInterface $messageBus,
        UserFetcherInterface $userFetcher,
        CreatePasswordJsonResponder $responder,
        TranslatorInterface $translator,
    ): CreatePasswordJsonResponder {
        try {
            $messageBus->dispatch(
                new CreateUserPasswordCommand(
                    $userFetcher->fetch()->id(),
                    $request->password,
                    $request->confirmationPassword,
                )
            );

            return $responder->success('Ok');
        } catch (\Throwable $throwable) {
            dump($throwable->getMessage()); die();
            return $responder->validationError($translator->trans('password_create_error'));
        }
    }
}
