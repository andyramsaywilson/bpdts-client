<?php
declare(strict_types = 1);

namespace App\EntityCollection;

use App\Entity\User;

class UserCollection
{
    private array $items = [];

    public function addItem(User $item): void
    {
        $this->items[] = $item;
    }

    /** @return User[] */
    public function getItems(): array
    {
        return $this->items;
    }
}
