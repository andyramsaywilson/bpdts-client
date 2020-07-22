<?php
declare(strict_types = 1);

namespace App\ApiClient\BpdtsTestApp;

use GuzzleHttp\Client;
use GuzzleHttp\Promise\PromiseInterface;

class ApiClient
{
    private Client $httpClient;
    private string $usersByCityUrl;
    private string $usersUrl;

    public function __construct(string $bpdtsApiUrl)
    {
        $this->httpClient = new Client(['base_uri' => $bpdtsApiUrl]);
        $this->usersByCityUrl = $bpdtsApiUrl . 'city/' . '{city}' . '/users';
        $this->usersUrl = $bpdtsApiUrl . '/users';
    }

    public function findUsersByCity(string $city): PromiseInterface
    {
        return $this->httpClient->getAsync(str_replace('{city}', $city, $this->usersByCityUrl));
    }

    public function findUsers(): PromiseInterface
    {
        return $this->httpClient->getAsync($this->usersUrl);
    }
}
