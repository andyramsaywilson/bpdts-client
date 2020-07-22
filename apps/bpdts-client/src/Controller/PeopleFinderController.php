<?php
declare(strict_types = 1);

namespace App\Controller;

use App\ApiClient\Async\PromiseCollection;
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
    private PromiseCollection $promises;

    public function __construct(
        DeduplicationService $duplicate,
        PeopleFinderService $service,
        PeopleFinderResponseBuilder $responseBuilder,
        PromiseCollection $promises
    ) {
        $this->duplicate = $duplicate;
        $this->service = $service;
        $this->responseBuilder = $responseBuilder;
        $this->promises = $promises;
    }

    public function getPeopleAsync(): Response
    {
        $city = 'London';
        $distance = 50;

        try {
            $this->promises->add($byCity = $this->service->findByCity($city));
            $this->promises->add($byLocation = $this->service->findByCurrentLocation($city, $distance));
            while (!$this->promises->resolve()) {
                usleep(10000);
            }
        } catch (UpstreamApiException | DataBoundaryTransformationFailedException $e) {
            return $this->responseBuilder->buildBadGatewayResponse($e);
        } catch (RequiredLookupFailedException | UnexpectedLogicException $e) {
            return $this->responseBuilder->buildInternalServerErrorResponse($e);
        }

        $results = $this->combineResults(
            $this->requireUserCollection($byCity->getParsedResponse()),
            $this->requireUserCollection($byLocation->getParsedResponse())
        );
        $response = $this->transformResults($results);
        return $this->responseBuilder->buildSuccessResponse($response);
    }

    private function requireUserCollection(object $object): UserCollection
    {
        /** @var UserCollection $object */
        return $object;
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
