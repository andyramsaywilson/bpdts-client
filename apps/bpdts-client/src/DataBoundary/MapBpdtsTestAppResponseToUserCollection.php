<?php
declare(strict_types = 1);

namespace App\DataBoundary;

use App\Entity\User;
use App\EntityCollection\UserCollection;

class MapBpdtsTestAppResponseToUserCollection
{
    public function map(string $jsonResponse, UserCollection $destination): void
    {
        $users = json_decode($jsonResponse, true);
        foreach ($users as $user) {
            $destination->addItem(
                new User(
                    (int)$user['id'],
                    $user['first_name'],
                    $user['last_name'],
                    $user['email'],
                    $user['ip_address'],
                    (float)$user['latitude'],
                    (float)$user['longitude']
                )
            );
        }
    }
}
