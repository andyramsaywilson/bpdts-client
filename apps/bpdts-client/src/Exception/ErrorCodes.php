<?php
declare(strict_types = 1);

namespace App\Exception;

class ErrorCodes
{
    const BPDTS_FIND_USERS_API_CALL_FAILED_MESSAGE = 'BPDTS FIND USERS API CALL FAILED';
    const BPDTS_FIND_USERS_API_CALL_FAILED_CODE = 1;

    const BPDTS_FIND_USERS_BY_CITY_API_CALL_FAILED_MESSAGE = 'BPDTS FIND USERS BY CITY API CALL FAILED';
    const BPDTS_FIND_USERS_BY_CITY_API_CALL_FAILED_CODE = 2;

    const LOCATION_GEOLOCATION_NOT_FOUND_MESSAGE = 'LOCATION GEOLOCATION NOT FOUND';
    const LOCATION_GEOLOCATION_NOT_FOUND_CODE = 3;

    const FIND_BY_CITY_TRANSFORMATION_FAILED_MESSAGE = 'FIND BY CITY TRANSFORMATION FAILED';
    const FIND_BY_CITY_TRANSFORMATION_FAILED_CODE = 4;

    const FIND_BY_CURRENT_LOCATION_TRANSFORMATION_FAILED_MESSAGE = 'FIND BY CURRENT LOCATION TRANSFORMATION FAILED';
    const FIND_BY_CURRENT_LOCATION_TRANSFORMATION_FAILED_CODE = 5;

    const API_RESPONSE_PAYLOAD_IS_NOT_VALID_JSON_MESSAGE = 'API RESPONSE PAYLOAD IS NOT VALID JSON';
    const API_RESPONSE_PAYLOAD_IS_NOT_VALID_JSON_CODE = 6;

    const REQUIRED_API_RESPONSE_FIELD_MISSING_MESSAGE = 'REQUIRED API RESPONSE FIELD MISSING';
    const REQUIRED_API_RESPONSE_FIELD_MISSING_CODE = 7;

    const REQUIRED_API_RESPONSE_FIELD_INVALID_MESSAGE = 'REQUIRED API RESPONSE FIELD INVALID';
    const REQUIRED_API_RESPONSE_FIELD_INVALID_CODE = 8;
}
