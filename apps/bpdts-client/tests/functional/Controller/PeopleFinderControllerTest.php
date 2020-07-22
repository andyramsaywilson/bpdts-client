<?php
declare(strict_types = 1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PeopleFinderControllerTest extends WebTestCase
{
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
