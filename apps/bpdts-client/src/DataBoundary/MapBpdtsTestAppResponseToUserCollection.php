<?php
declare(strict_types = 1);

namespace App\DataBoundary;

use App\Entity\User;
use App\EntityCollection\UserCollection;
use App\Exception\ErrorCodes;
use InvalidArgumentException;

class MapBpdtsTestAppResponseToUserCollection
{
    private array $fieldDefinitions = [
        'id' => 'int',
        'first_name' => 'string',
        'last_name' => 'string',
        'email' => 'string',
        'ip_address' => 'string',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    private array $validators = [
        'int' => 'set at run time',
        'float' => 'set at run time',
        'string' => 'set at run time',
    ];

    public function __construct()
    {
        $this->validators['int'] = function ($value) {
            return is_int($value);
        };
        $this->validators['float'] = function ($value) {
            return is_float($value) || is_int($value) || is_numeric($value);
        };
        $this->validators['string'] = function ($value) {
            return is_string($value);
        };
    }

    public function map(string $jsonResponse): UserCollection
    {
        $users = json_decode($jsonResponse, true);
        if (!is_array($users)) {
            throw new InvalidArgumentException(
                ErrorCodes::API_RESPONSE_PAYLOAD_IS_NOT_VALID_JSON_MESSAGE,
                ErrorCodes::API_RESPONSE_PAYLOAD_IS_NOT_VALID_JSON_CODE
            );
        }

        $result = new UserCollection();

        foreach ($users as $user) {
            $this->guardAgainstInvalidFields($user);
            $result->addItem(
                new User(
                    (int)$user['id'],
                    (string)$user['first_name'],
                    (string)$user['last_name'],
                    (string)$user['email'],
                    (string)$user['ip_address'],
                    (float)$user['latitude'],
                    (float)$user['longitude']
                )
            );
        }

        return $result;
    }

    private function guardAgainstInvalidFields(array $data): void
    {
        foreach ($this->fieldDefinitions as $fieldName => $dataType) {
            if (!isset($data[$fieldName])) {
                throw new InvalidArgumentException(
                    ErrorCodes::REQUIRED_API_RESPONSE_FIELD_MISSING_MESSAGE,
                    ErrorCodes::REQUIRED_API_RESPONSE_FIELD_MISSING_CODE
                );
            }
            $value = $data[$fieldName];
            $validator = $this->validators[$dataType];

            if (!$validator($value)) {
                throw new InvalidArgumentException(
                    ErrorCodes::REQUIRED_API_RESPONSE_FIELD_INVALID_MESSAGE,
                    ErrorCodes::REQUIRED_API_RESPONSE_FIELD_INVALID_CODE
                );
            }
        }
    }
}
