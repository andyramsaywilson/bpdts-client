<?php
declare(strict_types = 1);

namespace App\Controller;

use App\Entity\User;
use App\EntityCollection\UserCollection;
use App\Exception\DataBoundaryTransformationFailedException;
use App\Exception\RequiredLookupFailedException;
use App\Exception\UnexpectedLogicException;
use App\Exception\UpstreamApiException;
use App\ResponseBuilder\PeopleFinderResponseBuilder;
use App\Service\DeduplicationService;
use App\Service\PeopleFinderService;
use Symfony\Component\HttpFoundation\Response;

class PeopleFinderController
{
    private DeduplicationService $duplicate;
    private PeopleFinderService $service;
    private PeopleFinderResponseBuilder $responseBuilder;

    public function __construct(
        DeduplicationService $duplicate,
        PeopleFinderService $service,
        PeopleFinderResponseBuilder $responseBuilder
    ) {
        $this->duplicate = $duplicate;
        $this->service = $service;
        $this->responseBuilder = $responseBuilder;
    }

    public function getPeople(): Response
    {
        $city = 'London';
        $distance = 50;

        $promises = [
            'byCity' => $this->service->findByCity($city),
            'byLocation' => $this->service->findByCurrentLocation($city, $distance),
        ];
        try {
            $responses = \GuzzleHttp\Promise\unwrap($promises);
        }  catch (UpstreamApiException | DataBoundaryTransformationFailedException $e) {
            return $this->responseBuilder->buildBadGatewayResponse($e);
        } catch (RequiredLookupFailedException | UnexpectedLogicException $e) {
            return $this->responseBuilder->buildInternalServerErrorResponse($e);
        }

        $results = $this->combineResults($responses);
        $response = $this->transformResults($results);
        return $this->responseBuilder->buildSuccessResponse($response);
    }

    private function combineResults(array $userCollections): UserCollection
    {
        $results = new UserCollection();
        $this->duplicate->deduplicate(
            array_map(function(UserCollection $value): array {
                return $value->getItems();
            }, $userCollections),
            function (User $user): int {
                return $user->getId();
            },
            function (User $user) use ($results): void {
                $results->addItem($user);
            }
        );
        return $results;
    }

    private function transformResults(UserCollection $results): array
    {
        $data = [];
        foreach ($results->getItems() as $user) {
            $data[] = [
                'id' => $user->getId(),
                'first_name' => $user->getFirstName(),
                'last_name' => $user->getLastName(),
                'email' => $user->getEmail(),
                'ip_address' => $user->getIpAddress(),
                'latitude' => $user->getLatitude(),
                'longitude' => $user->getLongitude(),
            ];
        }
        return $data;
    }
}
