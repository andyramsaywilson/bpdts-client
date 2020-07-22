<?php
declare(strict_types = 1);

namespace App\Service;

use App\ApiClient\BpdtsTestApp\ApiClient;
use App\DataBoundary\MapBpdtsTestAppResponseToUserCollection;
use App\Entity\Geolocation;
use App\Entity\User;
use App\EntityCollection\UserCollection;
use App\Exception\DataBoundaryTransformationFailedException;
use App\Exception\ErrorCodes;
use App\Exception\RequiredLookupFailedException;
use App\Exception\UpstreamApiException;
use App\Repository\GeolocationRepository;
use GuzzleHttp\Promise\PromiseInterface;
use Exception;
use Psr\Http\Message\ResponseInterface;

class PeopleFinderService
{
    private ApiClient $apiClient;
    private MapBpdtsTestAppResponseToUserCollection $apiDataMapper;
    private GeolocationRepository $geolocationRepository;
    private GeolocationService $geolocationService;

    public function __construct(
        ApiClient $apiClient,
        MapBpdtsTestAppResponseToUserCollection $apiDataMapper,
        GeolocationRepository $geolocationRepository,
        GeolocationService $geolocationService
    ) {
        $this->apiClient = $apiClient;
        $this->apiDataMapper = $apiDataMapper;
        $this->geolocationRepository = $geolocationRepository;
        $this->geolocationService = $geolocationService;
    }

    public function findByCity(string $cityName): PromiseInterface
    {
        $promise = $this->apiClient->findUsersByCity($cityName);
        return $promise->then(
            function (ResponseInterface $response) {
                try {
                    return $this->apiDataMapper->map((string)$response->getBody());
                } catch (Exception $e) {
                    throw new DataBoundaryTransformationFailedException(
                        ErrorCodes::FIND_BY_CITY_TRANSFORMATION_FAILED_MESSAGE,
                        ErrorCodes::FIND_BY_CITY_TRANSFORMATION_FAILED_CODE,
                        $e
                    );
                }
            },
            function (Exception $e) {
                throw new UpstreamApiException(
                    ErrorCodes::BPDTS_FIND_USERS_BY_CITY_API_CALL_FAILED_MESSAGE,
                    ErrorCodes::BPDTS_FIND_USERS_BY_CITY_API_CALL_FAILED_CODE,
                    $e
                );
            }
        );
    }

    public function findByCurrentLocation(string $locationName, float $maxAllowedDistanceInMiles): PromiseInterface
    {
        $targetLocation = $this->geolocationRepository->findByLocationName($locationName);
        if (!$targetLocation instanceof Geolocation) {
            throw new RequiredLookupFailedException(
                ErrorCodes::LOCATION_GEOLOCATION_NOT_FOUND_MESSAGE,
                ErrorCodes::LOCATION_GEOLOCATION_NOT_FOUND_CODE
            );
        }

        $filter = $this->buildIsUserWithinLocationBoundaryFilter($maxAllowedDistanceInMiles, $targetLocation);

        $promise = $this->apiClient->findUsers();
        return $promise->then(
            function (ResponseInterface $response) use ($filter) {
                try {
                    $allUsers = $this->apiDataMapper->map((string)$response->getBody());

                    $usersWithinLocationBoundary = new UserCollection();
                    foreach ($allUsers->getItems() as $user) {
                        if ($filter($user)) {
                            $usersWithinLocationBoundary->addItem($user);
                        }
                    }
                    return $usersWithinLocationBoundary;
                } catch (Exception $e) {
                    throw new DataBoundaryTransformationFailedException(
                        ErrorCodes::FIND_BY_CURRENT_LOCATION_TRANSFORMATION_FAILED_MESSAGE,
                        ErrorCodes::FIND_BY_CURRENT_LOCATION_TRANSFORMATION_FAILED_CODE,
                        $e
                    );
                }
            },
            function (Exception $e) {
                throw new UpstreamApiException(
                    ErrorCodes::BPDTS_FIND_USERS_API_CALL_FAILED_MESSAGE,
                    ErrorCodes::BPDTS_FIND_USERS_API_CALL_FAILED_CODE,
                    $e
                );
            }
        );
    }

    private function buildIsUserWithinLocationBoundaryFilter(float $maxAllowedDistanceInMiles, Geolocation $targetLocation): callable
    {
        return function (User $subject) use ($maxAllowedDistanceInMiles, $targetLocation): bool {
            return $this->geolocationService->isPointWithinDistance(
                $targetLocation->getLatitude(),
                $targetLocation->getLongitude(),
                $subject->getLatitude(),
                $subject->getLongitude(),
                $maxAllowedDistanceInMiles
            );
        };
    }
}
