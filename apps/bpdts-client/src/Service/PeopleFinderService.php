<?php
declare(strict_types = 1);

namespace App\Service;

use App\ApiClient\Async\Promise;
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
use Exception;

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

    public function findByCity(string $cityName): Promise
    {
        return new Promise(
            $this->apiClient->findUsersByCity($cityName),
            new UserCollection(),
            function(string $responseContent, UserCollection $usersInCity): void {
                $this->apiDataMapper->map($responseContent, $usersInCity);
            },
            function (Exception $e): void {
                throw new UpstreamApiException(
                    ErrorCodes::BPDTS_FIND_USERS_BY_CITY_API_CALL_FAILED_MESSAGE,
                    ErrorCodes::BPDTS_FIND_USERS_BY_CITY_API_CALL_FAILED_CODE,
                    $e
                );
            },
            function (Exception $e): void {
                throw new DataBoundaryTransformationFailedException(
                    ErrorCodes::FIND_BY_CITY_TRANSFORMATION_FAILED_MESSAGE,
                    ErrorCodes::FIND_BY_CITY_TRANSFORMATION_FAILED_CODE,
                    $e
                );
            }
        );
    }

    public function findByCurrentLocation(string $locationName, float $maxAllowedDistanceInMiles): Promise
    {
        $targetLocation = $this->geolocationRepository->findByLocationName($locationName);
        if (!$targetLocation instanceof Geolocation) {
            throw new RequiredLookupFailedException(
                ErrorCodes::LOCATION_GEOLOCATION_NOT_FOUND_MESSAGE,
                ErrorCodes::LOCATION_GEOLOCATION_NOT_FOUND_CODE
            );
        }

        $filter = $this->buildIsUserWithinLocationBoundaryFilter($maxAllowedDistanceInMiles, $targetLocation);

        return new Promise(
            $this->apiClient->findUsers(),
            new UserCollection(),
            function(string $responseContent, UserCollection $usersWithinLocationBoundary) use ($filter): void {
                $allUsers = new UserCollection();
                $this->apiDataMapper->map($responseContent, $allUsers);

                foreach ($allUsers->getItems() as $user) {
                    if ($filter($user)) {
                        $usersWithinLocationBoundary->addItem($user);
                    }
                }
            },
            function (Exception $e): void {
                throw new UpstreamApiException(
                    ErrorCodes::BPDTS_FIND_USERS_API_CALL_FAILED_MESSAGE,
                    ErrorCodes::BPDTS_FIND_USERS_API_CALL_FAILED_CODE,
                    $e
                );
            },
            function (Exception $e): void {
                throw new DataBoundaryTransformationFailedException(
                    ErrorCodes::FIND_BY_CURRENT_LOCATION_TRANSFORMATION_FAILED_MESSAGE,
                    ErrorCodes::FIND_BY_CURRENT_LOCATION_TRANSFORMATION_FAILED_CODE,
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
