<?php

declare(strict_types=1);

namespace Atto\Framework\Response;

use Atto\Framework\Response\Errors\ErrorConverter;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\ResponseInterface;

final class Builder
{
    public function __construct(
        private ErrorConverter $errorHandler,
        private Psr17Factory $psr17Factory
    ) {
    }

    public function buildResponse(mixed $result): ResponseInterface
    {
        if ($result instanceof ResponseInterface) {
            return $result;
        } elseif ($result instanceof \Throwable) {
            $response = $this->errorHandler->convertFromThrowable($result);
        } elseif ($result instanceof HasResponseInfo) {
            $response = $this->psr17Factory->createResponse($result->getStatusCode())->withBody(
                $this->psr17Factory->createStream(\json_encode($result))
            )->withHeader('Content-Type', 'application/json');
        } else {
            $response = $this->psr17Factory->createResponse(200)->withBody(
                $this->psr17Factory->createStream(\json_encode($result))
            )->withHeader('Content-Type', 'application/json');
        }

        return $response;
    }
}
