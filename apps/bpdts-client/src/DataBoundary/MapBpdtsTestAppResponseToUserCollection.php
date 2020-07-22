<?php
declare(strict_types = 1);

namespace App\DataBoundary;

use App\Entity\User;
use App\EntityCollection\UserCollection;

class MapBpdtsTestAppResponseToUserCollection
{
    public function map(string $jsonResponse, UserCollection $destination): void
    {
        $destination->addItem(new User($jsonResponse));
    }
}
