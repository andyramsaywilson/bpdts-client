<?php
declare(strict_types = 1);

namespace App\ApiClient\BpdtsTestApp;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use InvalidArgumentException;

class MockHttpClientFactory
{
    public const CASE_TWO_USERS_NEITHER_LONDON = '0001';
    public const CASE_TWO_USERS_BOTH_LONDON = '0002';
    public const CASE_TWO_USERS_ONE_LONDON = '0003';
    public const CASE_EMPTY_RESPONSE = '5001';
    public const CASE_INVALID_JSON = '9001';

    public const CASE_DNS_ERROR = 'DNS_ERROR';
    public const CASE_HTTP_RESPONSE_404 = '404';
    public const CASE_HTTP_RESPONSE_403 = '403';
    public const CASE_HTTP_RESPONSE_500 = '403';

    private array $httpSuccess = [
        self::CASE_TWO_USERS_NEITHER_LONDON,
        self::CASE_TWO_USERS_BOTH_LONDON,
        self::CASE_TWO_USERS_ONE_LONDON,
        self::CASE_EMPTY_RESPONSE,
        self::CASE_INVALID_JSON,
        self::CASE_TWO_USERS_NEITHER_LONDON,
    ];

    private array $httpError = [
        self::CASE_DNS_ERROR,
        self::CASE_HTTP_RESPONSE_403,
        self::CASE_HTTP_RESPONSE_404,
        self::CASE_HTTP_RESPONSE_500,
    ];

    public function createMockedSuccessResponse(array $caseNames): Client
    {
        $mocks = [];
        foreach ($caseNames as $caseName) {
            $mocks[] = $this->mapCaseNameToResponse($caseName);
        }

        $mock = new MockHandler($mocks);
        return new Client(['handler' => HandlerStack::create($mock)]);
    }

    private function mapCaseNameToResponse(string $caseName)
    {
        if (in_array($caseName, $this->httpSuccess)) {
            return new Response(200, [], $this->includeResponseTemplate($caseName));
        }
        if (in_array($caseName, $this->httpError)) {
            switch($caseName) {
                case self::CASE_HTTP_RESPONSE_403:
                    return new Response(403);
                case self::CASE_HTTP_RESPONSE_404:
                    return new Response(404);
                case self::CASE_HTTP_RESPONSE_500:
                    return new Response(500);
                case self::CASE_DNS_ERROR:
                    return new RequestException('Error Communicating with Server', new Request('GET', 'test'));
            }
        }
        throw new InvalidArgumentException("Case {$caseName} is not configured");
    }

    private function includeResponseTemplate($name): string
    {
        $dir = __DIR__ . '/MockResponses/';
        $files = scandir($dir);
        foreach ($files as $file) {
            // naming convention of <name>.label.json used
            $parts = explode('.', $file);
            if (count($parts) !== 3) {
                continue;
            }
            if ($parts[0] === $name) {
                return file_get_contents($dir. $file);
            }
        }
        throw new InvalidArgumentException("No mock found for name {$name} in path {$dir}");
    }
}
