<?php
declare(strict_types = 1);

namespace App\Service;

use PHPUnit\Framework\TestCase;

class GeolocationServiceTest extends TestCase
{
    private GeolocationService $sut;

    public function setUp(): void
    {
        $this->sut = new GeolocationService();
    }

    /** @dataProvider providerDistanceBetweenTwoPointsInMiles */
    public function testDistanceBetweenTwoPointsInMiles(float $fromLat, float $fromLong, float $toLat, float $toLong, float $distance, bool $expected): void
    {
        $actual = $this->sut->isPointWithinDistance($fromLat, $fromLong, $toLat, $toLong, $distance);
        $this->assertSame($expected, $actual);
    }

    public function providerDistanceBetweenTwoPointsInMiles(): array
    {
        return [
            'Lands End to John O\'Groats is 601.78 miles - exact match fails due to floating point rounding error' => [50.067833062, -5.709663828, 58.642334, -3.070539, 601.78119218818, false],
            'Lands End to John O\'Groats is 601.78 miles - just inside' => [50.067833062, -5.709663828, 58.642334, -3.070539, 601.78119218819, true],
            'Lands End to John O\'Groats is 601.78 miles - just outside' => [50.067833062, -5.709663828, 58.642334, -3.070539, 601.78119218817, false],
            'London to Leeds is 169.29 miles to 2dp - inside' => [51.509865, -0.118092, 53.801277, -1.548567, 170.0, true],
            'Leeds to London is 169.29 miles to 2dp - outside' => [53.801277, -1.548567, 51.509865, -0.118092, 168.0, false],
            'London to Wembley Stadium is 7.84 miles to 2dp - well inside' => [51.509865, -0.118092, 51.562023, -0.280151, 50.0, true],
            'London to Old Trafford is 162.63 miles to 2dp - well outside' => [51.509865, -0.118092, 53.457831502, -2.288165514, 50.0, false],
        ];
    }
}
