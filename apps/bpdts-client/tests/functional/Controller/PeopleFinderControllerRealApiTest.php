<?php
declare(strict_types = 1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Test the application end-to-end hitting the real API
 * Tagged so that it can be run independently from the other tests, as it's useful but unreliable,
 * so it's the top of our 'testing pyramid' with just one test
 */
class PeopleFinderControllerRealApiTest extends WebTestCase
{
    /** @group realApi */
    public function testWithRealApi(): void
    {
        $client = static::createClient();
        $client->request('GET', '/people');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue(is_array($response));
        $this->assertEmpty($response['meta']);
        $this->assertFalse(isset($response['error']));
        $this->assertNotEmpty($response['data']);
        $this->assertCount(9, $response['data']);
    }
}
