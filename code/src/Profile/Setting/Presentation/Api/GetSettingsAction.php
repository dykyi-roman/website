<?php

declare(strict_types=1);

namespace Profile\Setting\Presentation\Api;

use OpenApi\Attributes as OA;
use Profile\Setting\Application\Settings\Query\GetSettingsQuery;
use Profile\Setting\Presentation\Api\Response\GetSettingsJsonResponder;
use Shared\DomainModel\Services\MessageBusInterface;
use Site\User\DomainModel\Service\UserFetcher;
use Symfony\Component\Routing\Attribute\Route;

final readonly class GetSettingsAction
{
    #[Route('/v1/settings', name: 'api_settings_get', methods: ['GET'])]
    #[OA\Get(
        path: '/v1/settings',
        description: 'Get all user settings grouped by category',
        summary: 'Get settings'
    )]
    public function __invoke(
        UserFetcher $userFetcher,
        MessageBusInterface $messageBus,
        GetSettingsJsonResponder $responder,
    ): GetSettingsJsonResponder {
        /** @var array<string, array<string, mixed>> $settings */
        $settings = $messageBus->dispatch(new GetSettingsQuery($userFetcher->fetch()->id()));

        return $responder->success($settings)->respond();
    }
}
