<?php
declare(strict_types = 1);

namespace App\Controller;

use App\Entity\User;
use App\EntityCollection\UserCollection;
use App\Exception\DataBoundaryTransformationFailedException;
use App\Exception\GeolocationCalculationFailedException;
use App\Exception\RequiredLookupFailedException;
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

    public function getPeople(): Response
    {
        $city = 'London';
        $distance = 50;

        try {
            $byCity = $this->service->findByCity($city);
        } catch (UpstreamApiException | DataBoundaryTransformationFailedException $e) {
            return $this->responseBuilder->buildBadGatewayResponse($e);
        }

        try {
            $byLocation = $this->service->findByCurrentLocation($city, $distance);
        } catch (UpstreamApiException | DataBoundaryTransformationFailedException $e) {
            return $this->responseBuilder->buildBadGatewayResponse($e);
        } catch (RequiredLookupFailedException | GeolocationCalculationFailedException $e) {
            return $this->responseBuilder->buildInternalServerErrorResponse($e);
        }

        $results = $this->combineResults($byCity, $byLocation);
        $response = $this->transformResults($results);
        return $this->responseBuilder->buildSuccessResponse($response);
    }

    private function combineResults(UserCollection $byCity, UserCollection $byLocation): UserCollection
    {
        $results = new UserCollection();
        $this->duplicate->deduplicate(
            [$byCity->getItems(), $byLocation->getItems()],
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
