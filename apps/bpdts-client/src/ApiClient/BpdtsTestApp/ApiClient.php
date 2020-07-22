<?php
declare(strict_types = 1);

namespace App\ApiClient\BpdtsTestApp;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class ApiClient
{
    private HttpClientInterface $httpClient;
    private string $usersByCityUrl;
    private string $usersUrl;

    public function __construct(HttpClientInterface $httpClient, string $bpdtsApiUrl)
    {
        $this->httpClient = $httpClient;
        $this->usersByCityUrl = $bpdtsApiUrl . 'city/' . '{city}' . '/users';
        $this->usersUrl = $bpdtsApiUrl;
    }

    public function findUsersByCity(string $city): ResponseInterface
    {
        return $this->httpClient->request('get', str_replace('{city}', $city, $this->usersByCityUrl));
    }

    public function findUsers(): ResponseInterface
    {
        return $this->httpClient->request('get', $this->usersUrl);
    }
}
