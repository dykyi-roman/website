<?php

declare(strict_types=1);

namespace Site\Location\Presentation\Api;

use Shared\DomainModel\Services\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/v1/location', name: 'api_location', methods: ['GET'])]
final readonly class DetectLocationAction
{
    public function __invoke(
        MessageBusInterface $messageBus,
    ) {
        // TODO: Implement __invoke() method.
    }
}