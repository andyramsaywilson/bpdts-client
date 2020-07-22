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
use App\Exception\GeolocationCalculationFailedException;
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

    /**
     * @param string $cityName
     * @return UserCollection
     * @throws DataBoundaryTransformationFailedException
     * @throws UpstreamApiException
     */
    public function findByCity(string $cityName): UserCollection
    {
        try {
            $responseContent = $this->apiClient->findUsersByCity($cityName);
        } catch (Exception $e) {
            throw new UpstreamApiException(
                ErrorCodes::BPDTS_FIND_USERS_BY_CITY_API_CALL_FAILED_MESSAGE,
                ErrorCodes::BPDTS_FIND_USERS_BY_CITY_API_CALL_FAILED_CODE,
                $e
            );
        }

        try {
            return $this->apiDataMapper->map($responseContent);
        } catch (Exception $e) {
            throw new DataBoundaryTransformationFailedException(
                ErrorCodes::FIND_BY_CITY_TRANSFORMATION_FAILED_MESSAGE,
                ErrorCodes::FIND_BY_CITY_TRANSFORMATION_FAILED_CODE,
                $e
            );
        }
    }

    /**
     * @param string $locationName
     * @param float $maxAllowedDistanceInMiles
     * @return UserCollection
     * @throws DataBoundaryTransformationFailedException
     * @throws RequiredLookupFailedException
     * @throws UpstreamApiException
     * @throws GeolocationCalculationFailedException
     */
    public function findByCurrentLocation(string $locationName, float $maxAllowedDistanceInMiles): UserCollection
    {
        $targetLocation = $this->geolocationRepository->findByLocationName($locationName);
        if (!$targetLocation instanceof Geolocation) {
            throw new RequiredLookupFailedException(
                ErrorCodes::LOCATION_GEOLOCATION_NOT_FOUND_MESSAGE,
                ErrorCodes::LOCATION_GEOLOCATION_NOT_FOUND_CODE
            );
        }

        try {
            $responseContent = $this->apiClient->findUsers();
        } catch (Exception $e) {
            throw new UpstreamApiException(
                ErrorCodes::BPDTS_FIND_USERS_API_CALL_FAILED_MESSAGE,
                ErrorCodes::BPDTS_FIND_USERS_API_CALL_FAILED_CODE,
                $e
            );
        }

        try {
            $allUsers = $this->apiDataMapper->map($responseContent);
        } catch (Exception $e) {
            throw new DataBoundaryTransformationFailedException(
                ErrorCodes::FIND_BY_CURRENT_LOCATION_TRANSFORMATION_FAILED_MESSAGE,
                ErrorCodes::FIND_BY_CURRENT_LOCATION_TRANSFORMATION_FAILED_CODE,
                $e
            );
        }

        $filter = $this->buildIsUserWithinLocationBoundaryFilter($maxAllowedDistanceInMiles, $targetLocation);

        $usersWithinLocationBoundary = new UserCollection();
        foreach ($allUsers->getItems() as $user) {
            if ($filter($user)) {
                $usersWithinLocationBoundary->addItem($user);
            }
        }

        return $usersWithinLocationBoundary;
    }

    private function buildIsUserWithinLocationBoundaryFilter(float $maxAllowedDistanceInMiles, Geolocation $targetLocation): callable
    {
        return function(User $subject) use ($maxAllowedDistanceInMiles, $targetLocation): bool {
            try {
                $actualDistance = $this->geolocationService->findDistanceBetweenTwoPointsInMiles(
                    $targetLocation->getLatitude(),
                    $targetLocation->getLongitude(),
                    $subject->getLatitude(),
                    $subject->getLongitude()
                );

                return $actualDistance <= $maxAllowedDistanceInMiles;
            } catch (Exception $e) {
                throw new GeolocationCalculationFailedException(
                    ErrorCodes::APPLY_USER_WITHIN_LOCATION_BOUNDARY_FILTER_MESSAGE,
                    ErrorCodes::APPLY_USER_WITHIN_LOCATION_BOUNDARY_FILTER_CODE
                );
            }
        };
    }
}
