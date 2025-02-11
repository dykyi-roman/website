<?php

declare(strict_types=1);

namespace Profile\Setting\Presentation\Api;

use OpenApi\Attributes as OA;
use Profile\Setting\Application\Settings\Command\ChangePropertyCommand;
use Profile\Setting\Presentation\Api\Request\ChangeSettingRequestDto;
use Profile\Setting\Presentation\Api\Response\ChangeSettingJsonResponder;
use Shared\DomainModel\Services\MessageBusInterface;
use Shared\DomainModel\Services\UserFetcherInterface;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

final readonly class ChangeSettingAction
{
    #[Route('/v1/profile/settings', name: 'api_profile_settings_change', methods: ['PUT'])]
    #[OA\Put(
        path: '/api/v1/profile/settings',
        description: 'Updates settings based on provided parameters',
        summary: 'Change settings',
        tags: ['Profile']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'settings',
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(
                                property: 'category',
                                type: 'string',
                                enum: ['GENERAL', 'ACCOUNT', 'NOTIFICATION'],
                                example: 'NOTIFICATION'
                            ),
                            new OA\Property(
                                property: 'name',
                                type: 'string',
                                enum: ['phone_verified_at', 'email_verified_at', 'accepted_cookies'],
                                example: 'accepted_cookies'
                            ),
                            new OA\Property(
                                property: 'value',
                                type: 'mixed',
                                example: true
                            ),
                        ]
                    )
                ),
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Setting updated successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(property: 'message', type: 'string', example: 'Setting updated successfully'),
            ]
        )
    )]
    #[OA\Response(
        response: 400,
        description: 'Validation error',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: false),
                new OA\Property(property: 'message', type: 'string', example: 'Validation failed'),
                new OA\Property(
                    property: 'errors',
                    type: 'array',
                    items: new OA\Items(type: 'string')
                ),
            ]
        )
    )]
    public function __invoke(
        #[MapRequestPayload] ChangeSettingRequestDto $request,
        UserFetcherInterface $userFetcher,
        MessageBusInterface $messageBus,
        ChangeSettingJsonResponder $responder,
    ): ChangeSettingJsonResponder {
        $user = $userFetcher->fetch();

        $messageBus->dispatch(new ChangePropertyCommand(
            $user->id(),
            $request->properties(),
        ));

        return $responder->success('Ok')->respond();
    }
}
