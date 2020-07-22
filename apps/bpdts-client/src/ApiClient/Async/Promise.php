<?php
declare(strict_types = 1);

namespace App\ApiClient\Async;

use Exception;
use Symfony\Contracts\HttpClient\ChunkInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class Promise
{
    private ResponseInterface $httpResponse;
    private object $parsedResponse;
    private Callable $onSuccessHandler;
    private Callable $onApiErrorHandler;
    private Callable $onApiResponseParseErrorHandler;
    private bool $handled = false;

    public function __construct(
        ResponseInterface $httpResponse,
        object $parsedResponse,
        callable $onSuccessHandler,
        callable $onApiErrorHandler,
        callable $onApiResponseParseErrorHandler
    ) {
        $this->httpResponse = $httpResponse;
        $this->parsedResponse = $parsedResponse;
        $this->onSuccessHandler = $onSuccessHandler;
        $this->onApiErrorHandler = $onApiErrorHandler;
        $this->onApiResponseParseErrorHandler = $onApiResponseParseErrorHandler;
    }

    public function onCheckComplete(ChunkInterface $chunk): bool
    {
        if (!$this->handled) {
            $responseContent = null;
            try {
                if ($chunk->isFirst()) {
                    $this->httpResponse->getStatusCode();
                }
                if ($chunk->isLast()) {
                    $responseContent = $this->httpResponse->getContent();
                }
            } catch (Exception $e) {
                $this->onApiErrorHandler($e);
                $this->handled = true;
            }

            if (!is_null($responseContent)) {
                try {
                    $this->onSuccessHandler($responseContent, $this->parsedResponse);
                    $this->handled = true;
                } catch (Exception $e) {
                    $this->onApiResponseParseErrorHandler($e);
                    $this->handled = true;
                }
            }
        }
        return $this->handled;
    }

    public function getHttpResponse(): ResponseInterface
    {
        return $this->httpResponse;
    }

    public function getParsedResponse(): object
    {
        return $this->parsedResponse;
    }
}