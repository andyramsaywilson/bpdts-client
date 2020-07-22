<?php
declare(strict_types = 1);

namespace App\Promise;

use App\Exception\ErrorCodes;
use App\Exception\UnexpectedLogicException;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class PromiseCollection
{
    private HttpClientInterface $httpClient;
    /** @var Promise[]  */
    private array $promises = [];
    /** @var ResponseInterface[]  */
    private array $responses = [];

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function add(Promise $promise): void
    {
        $this->promises[] = $promise;
        $this->responses[] = $promise->getHttpResponse();
    }

    private function findPromiseForResponse(ResponseInterface $response): Promise
    {
        foreach ($this->promises as $promise) {
            if ($response === $promise->getHttpResponse()) {
                return $promise;
            }
        }
        throw new UnexpectedLogicException(ErrorCodes::PROMISE_NOT_FOUND_FOR_REQUEST_MESSAGE, ErrorCodes::PROMISE_NOT_FOUND_FOR_REQUEST_CODE);
    }

    public function resolve(): bool
    {
        $allComplete = true;
        foreach ($this->httpClient->stream($this->responses) as $response => $chunk) {
            $promise = $this->findPromiseForResponse($response);
            if (!$promise->onCheckComplete($chunk)) {
                $allComplete = false;
            }
        }
        return $allComplete;
    }
}