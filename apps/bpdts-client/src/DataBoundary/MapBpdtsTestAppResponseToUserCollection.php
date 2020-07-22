<?php
declare(strict_types = 1);

namespace App\DataBoundary;

use App\EntityCollection\UserCollection;

class MapBpdtsTestAppResponseToUserCollection
{
    public function map(string $jsonResponse): UserCollection
    {
        $result = new UserCollection();
        return $result;
    }
}
