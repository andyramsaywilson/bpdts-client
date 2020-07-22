<?php
declare(strict_types = 1);

namespace App\Repository;

use App\Entity\Geolocation;

class GeolocationRepository
{
    public function findByLocationName(string $locationName): ?Geolocation
    {
        if ($locationName === 'London') {
            return new Geolocation(51.509865, -0.118092);
        }
        return null;
    }
}
