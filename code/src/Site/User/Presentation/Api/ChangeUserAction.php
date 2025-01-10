<?php

declare(strict_types=1);

namespace Site\User\Presentation\Api;

use Shared\DomainModel\Services\MessageBusInterface;
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

        return $responder->success('Ok')->respond();
    }
}