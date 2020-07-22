<?php
declare(strict_types = 1);

namespace App\Controller;

use App\ApiClient\BpdtsTestApp\ApiClient;
use App\ApiClient\BpdtsTestApp\MockHttpClientFactory;
use App\Exception\ErrorCodes;
use ReflectionProperty;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response as ApiResponse;

/**
 * We can use the Framework's Dependency Injection features to inject a stubbed version of the http client only.
 * These tests still execute quickly, and allow full end-to-end testing against just a mocked API
 */
class PeopleFinderControllerMockedApiTest extends WebTestCase
{
    private KernelBrowser $client;

    public function setUp(): void
    {
        $this->client = $client = static::createClient();
    }

    public function testEmptyResultsAreValid(): void
    {
        $this->stubApiClient([
            MockHttpClientFactory::CASE_EMPTY_RESPONSE,
            MockHttpClientFactory::CASE_EMPTY_RESPONSE,
        ]);
        $response = $this->callApi();
        $this->assertSuccessResponse($response, 0);
    }

    public function testWithin50MilesOfLondonAreIncluded(): void
    {
        $this->stubApiClient([
            MockHttpClientFactory::CASE_EMPTY_RESPONSE,
            MockHttpClientFactory::CASE_TWO_USERS_BOTH_LONDON,
        ]);
        $response = $this->callApi();
        $this->assertSuccessResponse($response, 2);
    }

    public function testOutsideOf50MilesOfLondonAreExcluded(): void
    {
        $this->stubApiClient([
            MockHttpClientFactory::CASE_EMPTY_RESPONSE,
            MockHttpClientFactory::CASE_TWO_USERS_NEITHER_LONDON,
        ]);
        $response = $this->callApi();
        $this->assertSuccessResponse($response, 0);
    }

    public function testGeolocationFilterNotAppliedToResultsFromCityApi(): void
    {
        $this->stubApiClient([
            MockHttpClientFactory::CASE_TWO_USERS_NEITHER_LONDON,
            MockHttpClientFactory::CASE_EMPTY_RESPONSE,
        ]);
        $response = $this->callApi();
        $this->assertSuccessResponse($response, 2);
    }

    public function testDifferentResultsReturnedFromEachApiAreMerged(): void
    {
        $this->stubApiClient([
            MockHttpClientFactory::CASE_TWO_USERS_NEITHER_LONDON,
            MockHttpClientFactory::CASE_TWO_USERS_BOTH_LONDON,
        ]);
        $response = $this->callApi();
        $this->assertSuccessResponse($response, 4);
    }

    public function testLondonResultsArePartiallyReturnedWhenPartiallyMatching(): void
    {
        $this->stubApiClient([
            MockHttpClientFactory::CASE_EMPTY_RESPONSE,
            MockHttpClientFactory::CASE_TWO_USERS_ONE_LONDON,
        ]);
        $response = $this->callApi();
        $this->assertSuccessResponse($response, 1);
    }

    public function testUsersReturnedByBothUpstreamApisAreOnlyReturnedOnceEach(): void
    {
        $this->stubApiClient([
            MockHttpClientFactory::CASE_TWO_USERS_BOTH_LONDON,
            MockHttpClientFactory::CASE_TWO_USERS_BOTH_LONDON,
        ]);
        $response = $this->callApi();
        $this->assertSuccessResponse($response, 2);
    }

    public function testUpstreamCityApiInternalServerErrorHandledGracefully(): void
    {
        $this->stubApiClient([
            MockHttpClientFactory::CASE_DNS_ERROR,
        ]);
        $response = $this->callApi();
        $this->assertErrorResponse($response, 502, 1, ErrorCodes::BPDTS_FIND_USERS_BY_CITY_API_CALL_FAILED_CODE);
    }

    public function testUpstreamLocationApiInternalServerErrorHandledGracefully(): void
    {
        $this->stubApiClient([
            MockHttpClientFactory::CASE_EMPTY_RESPONSE,
            MockHttpClientFactory::CASE_DNS_ERROR,
        ]);
        $response = $this->callApi();
        $this->assertErrorResponse($response, 502, 1, ErrorCodes::BPDTS_FIND_USERS_API_CALL_FAILED_CODE);
    }

    private function stubApiClient(array $cases): void
    {
        $httpClient = (new MockHttpClientFactory())->createMockedSuccessResponse($cases);
        $apiClient = self::$container->get(ApiClient::class);
        $reflectionProperty = new ReflectionProperty(ApiClient::class, 'httpClient');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($apiClient, $httpClient);
    }

    private function callApi(): ApiResponse
    {
        $this->client->request('GET', '/people');
        return $this->client->getResponse();
    }

    private function assertSuccessResponse(ApiResponse $response, int $successCount): void
    {
        $this->assertEquals(200, $response->getStatusCode());
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertCount($successCount, $response['data']);
        $this->assertFalse(isset($response['errors']));
    }

    private function assertErrorResponse(ApiResponse $response, int $httpStatusCode, int $errorCount, int $errorCode): void
    {
        $this->assertEquals($httpStatusCode, $response->getStatusCode());
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertCount($errorCount, $response['errors']);
        $this->assertSame($errorCode, $response['errors'][0]['code']);
        $this->assertFalse(isset($response['data']));
    }
}
