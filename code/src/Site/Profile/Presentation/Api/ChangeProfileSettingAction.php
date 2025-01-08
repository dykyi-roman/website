<?php

declare(strict_types=1);

namespace Site\Profile\Presentation\Api;

use OpenApi\Attributes as OA;
use Site\Profile\DomainModel\Repository\SettingRepositoryInterface;
use Site\Profile\Presentation\Api\Request\ChangeProfileSettingRequest;
use Site\Profile\Presentation\Api\Response\ChangeSettingJsonResponder;
use Site\User\DomainModel\Model\User;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

final readonly class ChangeProfileSettingAction
{
    public function __construct(
        private SettingRepositoryInterface $settingRepository,
    ) {
    }

    #[Route('/v1/profile/settings', name: 'api_profile_settings_change', methods: ['PUT'])]
    #[OA\Put(
        path: '/v1/profile/settings',
        description: 'Updates profile settings based on provided parameters',
        summary: 'Change profile settings'
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
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
        #[MapRequestPayload] ChangeProfileSettingRequest $request,
        ChangeSettingJsonResponder $responder,
    ): ChangeSettingJsonResponder {
        $this->settingRepository->updateProperty(
            $user->getId(),
            $request->property(),
        );

        return $responder->success('Ok')->respond();
    }
}
