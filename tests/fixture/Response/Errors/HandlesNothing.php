<?php

declare(strict_types=1);

namespace Atto\Framework\Tests\Fixture\Response\Errors;

use Atto\Framework\Response\Errors\ErrorHandler;
use Crell\ApiProblem\ApiProblem;

final class HandlesNothing implements ErrorHandler
{
    public function supports(\Throwable $throwable): bool
    {
        return false;
    }

    public function handle(\Throwable $throwable): ApiProblem
    {
        throw new \RuntimeException('cannot handle anything');
    }
}
