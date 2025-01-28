<?php

declare(strict_types=1);

namespace Profile\User\Presentation\Api;

use OpenApi\Attributes as OA;
use Profile\User\Application\UserManagement\Command\ChangeUserPasswordCommand;
use Profile\User\Presentation\Api\Request\ChangePasswordRequestDto;
use Profile\User\Presentation\Api\Response\ChangePasswordJsonResponder;
use Shared\DomainModel\Services\MessageBusInterface;
use Shared\DomainModel\Services\UserFetcherInterface;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class ChangePasswordAction
{
    #[Route('/v1/profile/user/password', name: 'change_profile_user_password', methods: ['PUT'])]
    #[OA\Put(
        path: '/api/v1/profile/user/password',
        description: 'Changes the password for the authenticated user',
        summary: 'Change user password',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['currentPassword', 'newPassword'],
                properties: [
                    new OA\Property(property: 'currentPassword', type: 'string', example: 'current123'),
                    new OA\Property(property: 'newPassword', type: 'string', example: 'new123'),
                ]
            )
        ),
        tags: ['Profile'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Password changed successfully',
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
                        new OA\Property(property: 'message', type: 'string', example: 'Password change failed'),
                    ]
                )
            ),
        ]
    )]
    public function __invoke(
        #[MapRequestPayload] ChangePasswordRequestDto $request,
        MessageBusInterface $messageBus,
        UserFetcherInterface $userFetcher,
        ChangePasswordJsonResponder $responder,
        TranslatorInterface $translator,
    ): ChangePasswordJsonResponder {
        try {
            $messageBus->dispatch(
                new ChangeUserPasswordCommand(
                    $userFetcher->fetch()->id(),
                    $request->currentPassword,
                    $request->newPassword,
                )
            );

            return $responder->success('Ok');
        } catch (\Throwable) {
            return $responder->validationError($translator->trans('password_change_error'));
        }
    }
}
