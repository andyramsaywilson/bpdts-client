<?php
declare(strict_types = 1);

namespace App\ApiClient\BpdtsTestApp;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class ApiClient
{
    private HttpClientInterface $httpClient;
    private string $usersByCityUrl;
    private string $usersUrl;

    public function __construct(HttpClientInterface $httpClient, string $apiUrl)
    {
        $this->httpClient = $httpClient;
        $apiUrl = 'https://bpdts-test-app.herokuapp.com/';
        $this->usersByCityUrl = $apiUrl . 'city/' . '{city}' . '/users';
        $this->usersUrl = $apiUrl;
    }

    public function findUsersByCity(string $city): string
    {
        $response = $this->httpClient->request('get', str_replace('{city}', $city, $this->usersByCityUrl));
        return $response->getContent();
    }

    public function findUsers(): string
    {
        $response = $this->httpClient->request('get', $this->usersUrl);
        return $response->getContent();
    }
}
