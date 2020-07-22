<?php
declare(strict_types = 1);

namespace App\Repository;

use App\Entity\Geolocation;
use PHPUnit\Framework\TestCase;

class GeolocationRepositoryTest extends TestCase
{
    private GeolocationRepository $sut;

    public function setUp(): void
    {
        $this->sut = new GeolocationRepository();
    }

    public function testFindsLondon(): void
    {
        $this->assertEquals(
            new Geolocation(51.509865, -0.118092),
            $this->sut->findByLocationName('London')
        );
    }
    public function testReturnsNullOnNotFound(): void
    {
        $this->assertEquals(
            null,
            $this->sut->findByLocationName('Birmingham')
        );
    }

    public function testStringMatchingIsCaseSensitiveAsUpStreamApiIsToo(): void
    {
        $this->assertEquals(
            null,
            $this->sut->findByLocationName('LONDON')
        );
    }
}
