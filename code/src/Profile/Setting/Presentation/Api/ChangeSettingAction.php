<?php

declare(strict_types=1);

namespace Profile\Setting\Presentation\Api;

use OpenApi\Attributes as OA;
use Profile\Setting\Application\Settings\Command\ChangePropertyCommand;
use Profile\Setting\Presentation\Api\Request\ChangeSettingRequest;
use Profile\Setting\Presentation\Api\Response\ChangeSettingJsonResponder;
use Shared\DomainModel\Services\MessageBusInterface;
use Site\User\DomainModel\Model\User;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

final readonly class ChangeSettingAction
{
    public function __construct(
        private MessageBusInterface $messageBus,
    ) {
    }

    #[Route('/v1/settings', name: 'api_Setting_settings_change', methods: ['PUT'])]
    #[OA\Put(
        path: '/v1/settings',
        description: 'Updates Setting settings based on provided parameters',
        summary: 'Change Setting settings'
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
        #[CurrentUser] User $user,
        #[MapRequestPayload] ChangeSettingRequest $request,
        ChangeSettingJsonResponder $responder,
    ): ChangeSettingJsonResponder {
        $this->messageBus->dispatch(new ChangePropertyCommand(
            $user->getId(),
            $request->properties(),
        ));

        return $responder->success('Ok')->respond();
    }
}
