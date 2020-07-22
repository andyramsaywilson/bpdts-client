<?php
declare(strict_types = 1);

namespace App\Controller;

use App\ApiClient\BpdtsTestApp\ApiClient;
use App\ApiClient\BpdtsTestApp\MockHttpClientFactory;
use ReflectionProperty;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

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

        $this->client->request('GET', '/people');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertCount(0, $response['data']);
    }

    public function testWithin50MilesOfLondonAreIncluded(): void
    {
        $this->stubApiClient([
            MockHttpClientFactory::CASE_EMPTY_RESPONSE,
            MockHttpClientFactory::CASE_TWO_USERS_BOTH_LONDON,
        ]);

        $this->client->request('GET', '/people');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertCount(2, $response['data']);
    }

    public function testOutsideOf50MilesOfLondonAreExcluded(): void
    {
        $this->stubApiClient([
            MockHttpClientFactory::CASE_EMPTY_RESPONSE,
            MockHttpClientFactory::CASE_TWO_USERS_NEITHER_LONDON,
        ]);

        $this->client->request('GET', '/people');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertCount(0, $response['data']);
    }

    public function testGeolocationFilterNotAppliedToResultsFromCityApi(): void
    {
        $this->stubApiClient([
            MockHttpClientFactory::CASE_TWO_USERS_NEITHER_LONDON,
            MockHttpClientFactory::CASE_EMPTY_RESPONSE,
        ]);

        $this->client->request('GET', '/people');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertCount(2, $response['data']);
    }

    public function testDifferentResultsReturnedFromEachApiAreMerged(): void
    {
        $this->stubApiClient([
            MockHttpClientFactory::CASE_TWO_USERS_NEITHER_LONDON,
            MockHttpClientFactory::CASE_TWO_USERS_BOTH_LONDON,
        ]);

        $this->client->request('GET', '/people');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertCount(4, $response['data']);
    }

    public function testLondonResultsArePartiallyReturnedWhenPartiallyMatching(): void
    {
        $this->stubApiClient([
            MockHttpClientFactory::CASE_EMPTY_RESPONSE,
            MockHttpClientFactory::CASE_TWO_USERS_ONE_LONDON,
        ]);

        $this->client->request('GET', '/people');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertCount(1, $response['data']);
    }

    public function testUsersReturnedByBothUpstreamApisAreOnlyReturnedOnceEach(): void
    {
        $this->stubApiClient([
            MockHttpClientFactory::CASE_TWO_USERS_BOTH_LONDON,
            MockHttpClientFactory::CASE_TWO_USERS_BOTH_LONDON,
        ]);

        $this->client->request('GET', '/people');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertCount(2, $response['data']);
    }

    private function stubApiClient(array $cases): void
    {
        $httpClient = (new MockHttpClientFactory())->createMockedSuccessResponse($cases);
        $apiClient = self::$container->get(ApiClient::class);
        $reflectionProperty = new ReflectionProperty(ApiClient::class, 'httpClient');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($apiClient, $httpClient);
    }
}
