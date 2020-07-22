<?php
declare(strict_types = 1);

namespace App\Service;

use App\ApiClient\BpdtsTestApp\ApiClient;
use App\DataBoundary\MapBpdtsTestAppResponseToUserCollection;
use App\Repository\GeolocationRepository;
use PHPUnit\Framework\TestCase;

class PeopleFinderServiceTest extends TestCase
{
    private PeopleFinderService $sut;

    private ApiClient $apiClient;
    private MapBpdtsTestAppResponseToUserCollection $apiDataMapper;
    private GeolocationRepository $geolocationRepository;
    private GeolocationService $geolocationService;

    public function setUp(): void
    {
        $this->apiClient = $this->createMock(ApiClient::class);
        $this->apiDataMapper = $this->createMock(MapBpdtsTestAppResponseToUserCollection::class);
        $this->geolocationRepository = $this->createMock(GeolocationRepository::class);
        $this->geolocationService = $this->createMock(GeolocationService::class);
        $this->sut = new PeopleFinderService(
            $this->apiClient,
            $this->apiDataMapper,
            $this->geolocationRepository,
            $this->geolocationService
        );
    }
}
