<?php
declare(strict_types = 1);

namespace App\Entity;

use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testPropertiesSetOnConstruct(): void
    {
        $sut = new User(999, 'firstName', 'lastName', 'email', 'ipAddress', 1234.0, -2345.0);
        $this->assertSutMatchesDefaultExpectation($sut);
    }

    public function testIntegersConvertedToFloats(): void
    {
        $sut = new User(999, 'firstName', 'lastName', 'email', 'ipAddress', 1234, -2345);
        $this->assertSutMatchesDefaultExpectation($sut);
    }

    private function assertSutMatchesDefaultExpectation(User $sut): void
    {
        $this->assertSame(999, $sut->getId());
        $this->assertSame('firstName',$sut->getFirstName());
        $this->assertSame('lastName', $sut->getLastName());
        $this->assertSame('email', $sut->getEmail());
        $this->assertSame('ipAddress', $sut->getIpAddress());
        $this->assertSame(1234.0, $sut->getLatitude());
        $this->assertSame(-2345.0, $sut->getLongitude());
    }
}
