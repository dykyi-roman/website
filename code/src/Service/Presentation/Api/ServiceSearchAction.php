<?php

declare(strict_types=1);

namespace App\Service\Presentation\Api;

use OpenApi\Attributes as OA;
use App\Service\DomainModel\Service\ServiceInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class ServiceSearchAction extends AbstractApiAction
{
    #[OA\Get(
        path: '/api/service/search',
        summary: 'Test route message',
        tags: ['Test']
    )]
    #[OA\Response(
        response: 200,
        description: 'Success',
        content: new OA\JsonContent(type: 'string', example: 'Test')
    )]
    #[Route('/service/search', name: 'api_service_search', methods: ['GET'])]
    public function __invoke(
        Request $request,
        ServiceInterface $service,
    ): JsonResponse {
        $query = $request->query->get('query', '');
        
        $services = $service->search($query);
        
        return new JsonResponse($services);
    }
}