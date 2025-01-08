<?php

declare(strict_types=1);

namespace Site\Profile\Presentation\Api;

use OpenApi\Attributes as OA;
use Site\Profile\DomainModel\Repository\ProfileRepositoryInterface;
use Site\Profile\Presentation\Api\Request\ChangeProfileSettingRequest;
use Site\User\DomainModel\Model\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

final readonly class ChangeProfileSettingAction
{
    public function __construct(
        private ProfileRepositoryInterface $profileRepository,
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
                    property: 'group',
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
                    property: 'type',
                    type: 'string',
                    enum: ['string', 'integer', 'bool', 'date'],
                    example: 'bool'
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
        #[MapRequestPayload] ChangeProfileSettingRequest $request
    ): JsonResponse {
//        $this->profileRepository->addOrUpdate(
//            $user->getId(),
//            $request->property()
//        );

        return new JsonResponse([
            'success' => true,
            'message' => 'Setting updated successfully',
        ]);
    }
}
