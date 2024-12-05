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
        summary: 'Search services with pagination',
        tags: ['Service']
    )]
    #[OA\Parameter(
        name: 'query',
        description: 'Search query',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Parameter(
        name: 'page',
        description: 'Page number',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'integer', default: 1)
    )]
    #[OA\Parameter(
        name: 'limit',
        description: 'Items per page',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'integer', default: 10)
    )]
    #[OA\Response(
        response: 200,
        description: 'Success',
        content: new OA\JsonContent(type: 'object')
    )]
    #[Route('/service/search', name: 'api_service_search', methods: ['GET'])]
    public function __invoke(
        Request $request,
        ServiceInterface $service,
    ): JsonResponse {
        $query = $request->query->get('query', '');
        $page = (int) $request->query->get('page', 1);
        $limit = (int) $request->query->get('limit', 10);
        
        $result = $service->search($query, $page, $limit);
        
        return new JsonResponse($result);
    }
}
