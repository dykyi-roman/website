<?php

declare(strict_types=1);

namespace Profile\User\Presentation\Api;

use OpenApi\Attributes as OA;
use Profile\User\Application\UserManagement\Command\DeleteUserAccountCommand;
use Profile\User\Presentation\Api\Response\DeleteUserJsonResponder;
use Shared\DomainModel\Services\MessageBusInterface;
use Shared\DomainModel\Services\UserFetcherInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class DeleteUserAction
{
    #[Route('/v1/profile/user', name: 'api_user_delete', methods: ['DELETE'])]
    #[OA\Delete(
        path: '/api/v1/profile/user',
        description: 'Delete user account',
        summary: 'Delete user account',
        tags: ['Profile']
    )]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Account deleted successfully'
    )]
    public function __invoke(
        UserFetcherInterface $userFetcher,
        MessageBusInterface $messageBus,
        TranslatorInterface $translator,
        DeleteUserJsonResponder $responder,
    ): DeleteUserJsonResponder {
        try {
            $messageBus->dispatch(new DeleteUserAccountCommand($userFetcher->fetch()->id()));

            return $responder->success('Account deleted successfully')->respond();
        } catch (\Throwable) {
            return $responder->error($translator->trans('unexpected_registration_save_settings'))->respond();
        }
    }
}
