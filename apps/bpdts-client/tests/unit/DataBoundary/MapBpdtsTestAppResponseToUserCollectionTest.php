<?php
declare(strict_types = 1);

namespace App\DataBoundary;

use App\Entity\User;
use App\EntityCollection\UserCollection;
use App\Exception\ErrorCodes;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class MapBpdtsTestAppResponseToUserCollectionTest extends TestCase
{
    private MapBpdtsTestAppResponseToUserCollection $sut;

    public function setUp(): void
    {
        $this->sut = new MapBpdtsTestAppResponseToUserCollection();
    }

    public function testExceptionThrownIfNotJson(): void
    {
        $this->expectExceptionObject(new InvalidArgumentException(
            ErrorCodes::API_RESPONSE_PAYLOAD_IS_NOT_VALID_JSON_MESSAGE,
            ErrorCodes::API_RESPONSE_PAYLOAD_IS_NOT_VALID_JSON_CODE
        ));
        $this->sut->map('** INVALID JSON **');
    }

    public function testEmptyResponseLeavesResultEmpty(): void
    {
        $actual = $this->sut->map('[]');
        $this->assertSame([], $actual->getItems());
    }

    public function testResponseWithSingleItemReturnsResultWithSingleItem(): void
    {
        $actual = $this->sut->map(json_encode([$this->getJsonResponseItem(1)]));
        $this->assertCount(1, $actual->getItems());
    }

    public function testResponseWithMultipleItemsReturnsResultWithMultipleItems(): void
    {
        $actual = $this->sut->map(json_encode([
            $this->getJsonResponseItem(1),
            $this->getJsonResponseItem(2),
        ]));
        $this->assertCount(2, $actual->getItems());
    }

    public function testValidResponseDataIsMappedCorrectly(): void
    {
        $user = $this->getJsonResponseItem(1);
        $actualResults = $this->sut->map(json_encode([
            $this->getJsonResponseItem(1),
        ]))->getItems();
        /** @var User $actualUser */
        $actualUser = reset($actualResults);
        $this->assertSame($user['id'], $actualUser->getId());
        $this->assertSame($user['first_name'], $actualUser->getFirstName());
        $this->assertSame($user['last_name'], $actualUser->getLastName());
        $this->assertSame($user['email'], $actualUser->getEmail());
        $this->assertSame($user['ip_address'], $actualUser->getIpAddress());
        $this->assertSame($user['latitude'], $actualUser->getLatitude());
        $this->assertSame($user['longitude'], $actualUser->getLongitude());
    }

    /** @dataProvider providerMissingResponseFieldDataIsRejected */
    public function testMissingResponseFieldDataIsRejected(string $field): void
    {
        $this->expectExceptionObject(new InvalidArgumentException(
            ErrorCodes::REQUIRED_API_RESPONSE_FIELD_MISSING_MESSAGE,
            ErrorCodes::REQUIRED_API_RESPONSE_FIELD_MISSING_CODE
        ));

        $user = $this->getJsonResponseItem(1);
        unset($user[$field]);
        $this->sut->map(json_encode([
            $user,
        ]));
    }

    /** @dataProvider providerInvalidResponseFieldDataIsRejected */
    public function testInvalidResponseFieldDataIsRejected(string $field, $invalidValue): void
    {
        $this->expectExceptionObject(new InvalidArgumentException(
            ErrorCodes::REQUIRED_API_RESPONSE_FIELD_INVALID_MESSAGE,
            ErrorCodes::REQUIRED_API_RESPONSE_FIELD_INVALID_CODE
        ));

        $user = $this->getJsonResponseItem(1);
        $user[$field] = $invalidValue;
        $this->sut->map(json_encode([
            $user,
        ]));
    }

    public function providerMissingResponseFieldDataIsRejected(): array
    {
        return array_map(function($value) {
            return [$value[0]];
        }, $this->providerInvalidResponseFieldDataIsRejected());
    }

    public function providerInvalidResponseFieldDataIsRejected(): array
    {
        return [
            ['id', 'abcd'],
            ['first_name', 12345],
            ['last_name', 65412],
            ['email', 123.67],
            ['ip_address', 1893456],
            ['latitude', 'abcdefg'],
            ['longitude', 'gfedbca'],
        ];
    }

    private function getJsonResponseItem(int $seed): array
    {
        return [
            'id' => (int)(1000 + $seed),
            'first_name' => (string)('FIRST' . $seed),
            'last_name' => (string)('LAST' . $seed),
            'email' => (string)('email' .  $seed . '@test.com'),
            'ip_address' => (string)('192.168.222.' . (16 + $seed)),
            'latitude' => (float)(-1234.0 - $seed) * 1.0,
            'longitude' => (float)(1234.0 + $seed) * 1.0,
        ];
    }
}
