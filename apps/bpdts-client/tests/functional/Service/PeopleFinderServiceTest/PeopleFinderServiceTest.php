<?php
declare(strict_types = 1);

namespace App\Service;

use App\Exception\ErrorCodes;
use App\Exception\RequiredLookupFailedException;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PeopleFinderFunctionalServiceTest extends WebTestCase
{
    private PeopleFinderService $sut;

    public function setUp(): void
    {
        self::bootKernel();
        $sut = self::$container->get(PeopleFinderService::class);
        $this->assertInstanceOf(PeopleFinderService::class, $sut);
        /** @var PeopleFinderService $sut */
        $this->sut = $sut;
    }

    public function testCanInstantiate(): void
    {
        $this->assertInstanceOf(PeopleFinderService::class, $this->sut);
    }

    public function testExceptionThrownIfNotLondon(): void
    {
        $this->expectExceptionObject(
            new RequiredLookupFailedException(
            ErrorCodes::LOCATION_GEOLOCATION_NOT_FOUND_MESSAGE,
            ErrorCodes::LOCATION_GEOLOCATION_NOT_FOUND_CODE
            )
        );

        $this->sut->findByCurrentLocation('Not London', 12.34);
    }
}
