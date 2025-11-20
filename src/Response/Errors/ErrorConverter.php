<?php

declare(strict_types=1);

namespace Atto\Framework\Response\Errors;

use Crell\ApiProblem\ApiProblem;
use Crell\ApiProblem\HttpConverter;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\ResponseInterface;

final class ErrorConverter
{
    private HttpConverter $httpConverter;
    /** @var array<ErrorHandler> */
    private array $handlers;
    private readonly bool $debug;

    public function __construct(
        Psr17Factory $psr17Factory,
        array $handlers = [],
        bool $debug = false,
    ) {
        assert((fn(ErrorHandler $handler) => true)(...$handlers));
        $this->httpConverter = new HttpConverter($psr17Factory);
        $this->handlers = $handlers;
        $this->debug = $debug;
    }

    public function convertFromThrowable(\Throwable $throwable): ResponseInterface
    {
        foreach ($this->handlers as $handler) {
            if ($handler->supports($throwable)) {
                $problem = $handler->handle($throwable);
                break;
            }
        }

        return $this->httpConverter->toJsonResponse($problem ?? $this->buildDefaultProblem($throwable));
    }

    private function buildDefaultProblem(\Throwable $throwable): ApiProblem
    {
        $problem = new ApiProblem('Internal Server Error', 'about:blank');
        $problem->setStatus(500);
        $problem->setDetail('An unhandled exception was raised');

        if ($this->debug) {
            //@TODO improve this: add previous exceptions, exception code, class etc
            $problem['exception'] = [
                'trace' => $throwable->getTrace(),
                'message' => $throwable->getMessage(),
            ];
        }
        return $problem;
    }
}
