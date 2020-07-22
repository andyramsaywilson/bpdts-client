<?php
declare(strict_types = 1);

namespace App\Service;

class GeolocationService
{
    public function isPointWithinDistance(float $fromLat, float $fromLong, float $toLat, float $toLong, float $maxAllowedDistanceInMiles): bool
    {
        return $this->haversineDistance($fromLat, $fromLong, $toLat, $toLong) <= $maxAllowedDistanceInMiles;
    }

    /**
     * Using the haversine function to calculate distance - code adapted from a google maps javascript example
     * @see https://cloud.google.com/blog/products/maps-platform/how-calculate-distances-map-maps-javascript-api
     */
    private function haversineDistance(float $fromLat, float $fromLong, float $toLat, float $toLong): float {
        $r = 3958.8; // Radius of the Earth in miles
        $rlat1 = $fromLat * (M_PI/180); // Convert degrees to radians
        $rlat2 = $toLat * (M_PI/180); // Convert degrees to radians
        $difflat = $rlat2-$rlat1; // Radian difference (latitudes)
        $difflon = ($toLong-$fromLong) * (M_PI/180); // Radian difference (longitudes)

        $d = 2 * $r * asin(sqrt(sin($difflat/2)*sin($difflat/2)+cos($rlat1)*cos($rlat2)*sin($difflon/2)*sin($difflon/2)));
        return (float)$d;
    }
}
