<?php
declare(strict_types = 1);

namespace App\EntityCollection;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserCollectionTest extends TestCase
{
    private UserCollection $sut;

    public function setUp(): void
    {
        $this->sut = new UserCollection();
    }

    public function testEmptyOnCreate(): void
    {
        $this->assertSame([], $this->sut->getItems());
    }

    public function testCanAddAndRetrieveOneItem(): void
    {
        $user1 = $this->createUser();
        $this->sut->addItem($user1);
        $this->assertSame([$user1], $this->sut->getItems());
    }

    public function testCanAddAndRetrieveMultipleItems(): void
    {
        $user1 = $this->createUser();
        $user2 = $this->createUser();
        $this->sut->addItem($user1);
        $this->sut->addItem($user2);
        $this->assertSame([$user1, $user2], $this->sut->getItems());
    }

    private function createUser(): User
    {
        return $this->createMock(User::class);
    }
}
