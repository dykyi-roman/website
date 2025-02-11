<?php

declare(strict_types=1);

namespace Profile\User\Presentation\Api;

use OpenApi\Attributes as OA;
use Profile\User\Application\UserManagement\Command\ActivateUserAccountCommand;
use Profile\User\Presentation\Api\Request\ChangeUserStatusDto;
use Profile\User\Presentation\Api\Response\ChangeUserStatusJsonResponder;
use Shared\DomainModel\Services\MessageBusInterface;
use Shared\DomainModel\Services\UserFetcherInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class ChangeUserStatusAction
{
    #[Route('/v1/profile/user/status', name: 'api_user_status', methods: ['PUT'])]
    #[OA\Put(
        path: '/api/v1/profile/user/status',
        description: 'Update user account status (activate/deactivate)',
        summary: 'Update user status',
        tags: ['Profile']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'status',
                    description: 'User status',
                    type: 'string',
                ),
            ]
        )
    )]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'User status updated successfully'
    )]
    public function updateStatus(
        #[MapRequestPayload] ChangeUserStatusDto $request,
        MessageBusInterface $messageBus,
        TranslatorInterface $translator,
        UserFetcherInterface $userFetcher,
        ChangeUserStatusJsonResponder $responder,
    ): ChangeUserStatusJsonResponder {
        try {
            $messageBus->dispatch(
                new ActivateUserAccountCommand(
                    $userFetcher->fetch()->id(),
                    $request->status(),
                ),
            );

            return $responder->success('User status updated successfully')->respond();
        } catch (\Throwable) {
            return $responder->error($translator->trans('unexpected_registration_save_settings'))->respond();
        }
    }
}
