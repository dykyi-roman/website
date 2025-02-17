<?php

declare(strict_types=1);

namespace Profile\Setting\Presentation\Api;

use OpenApi\Attributes as OA;
use Profile\Setting\Application\Settings\Query\GetSettingsQuery;
use Profile\Setting\Presentation\Api\Response\GetSettingsJsonResponder;
use Shared\DomainModel\Services\MessageBusInterface;
use Shared\DomainModel\Services\UserFetcherInterface;
use Symfony\Component\Routing\Attribute\Route;

final readonly class GetSettingsAction
{
    #[Route('/v1/profile/settings', name: 'api_profile_settings_get', methods: ['GET'])]
    #[OA\Get(
        path: '/api/v1/profile/settings',
        description: 'Get all user settings grouped by category',
        summary: 'Get settings',
        tags: ['Profile']
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns user settings grouped by category',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'data',
                    description: 'Settings data grouped by category',
                    type: 'object'
                ),
            ],
            type: 'object'
        )
    )]
    public function __invoke(
        UserFetcherInterface $userFetcher,
        MessageBusInterface $messageBus,
        GetSettingsJsonResponder $responder,
    ): GetSettingsJsonResponder {
        /** @var array<string, array<string, mixed>> $settings */
        $settings = $messageBus->dispatch(new GetSettingsQuery($userFetcher->fetch()->id()));

        return $responder->success($settings)->respond();
    }
}
