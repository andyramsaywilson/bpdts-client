<?php
declare(strict_types = 1);

namespace App\ResponseBuilder;

use App\EntityCollection\UserCollection;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class PeopleFinderResponseBuilder
{
    public function buildSuccessResponse(array $data): JsonResponse
    {
        return new JsonResponse(json_encode([
            'meta' => [],
            'data' => $data,
        ]), Response::HTTP_OK);
    }

    public function buildBadGatewayResponse(Exception $e): JsonResponse
    {
        return new JsonResponse(json_encode([
            'meta' => [],
            'errors' => [[
                'code' => $e->getCode(),
            ]],
        ]), Response::HTTP_BAD_GATEWAY);
    }

    public function buildInternalServerErrorResponse(Exception $e): JsonResponse
    {
        return new JsonResponse(json_encode([
            'meta' => [],
            'errors' => [[
                'code' => $e->getCode(),
            ]],
        ]), Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
