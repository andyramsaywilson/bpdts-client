<?php
declare(strict_types = 1);

namespace App\Entity;

use PHPUnit\Framework\TestCase;

class GeolocationTest extends TestCase
{
    public function testPropertiesSetOnConstruct(): void
    {
        $lat = -1234.0;
        $long = 1234.0;
        $sut = new Geolocation($lat, $long);
        $this->assertSame($lat, $sut->getLatitude());
        $this->assertSame($long, $sut->getLongitude());
    }

    public function testIntegersAreConvertedToFloatsImplicitly(): void
    {
        $sut = new Geolocation(1234, 4321);
        $this->assertSame(1234.0, $sut->getLatitude());
        $this->assertSame(4321.0, $sut->getLongitude());
    }

    /** @dataProvider providerValidValuesAreAccepted */
    public function testValidValuesAreAccepted(float $lat, float $long): void
    {
        $sut = new Geolocation($lat, $long);
        $this->assertSame($lat, $sut->getLatitude());
        $this->assertSame($long, $sut->getLongitude());
    }

    public function providerValidValuesAreAccepted(): array
    {
        return [
            'Lands end' => [50.067833062, -5.709663828],
            'London' => [51.509865, -0.118092],
        ];
    }
}