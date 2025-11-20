<?php

declare(strict_types=1);

namespace Atto\Framework\Tests\Fixture\Response\Errors;

use Atto\Framework\Response\Errors\ErrorHandler;
use Crell\ApiProblem\ApiProblem;

final class HandlesEverything implements ErrorHandler
{
    public const TITLE = 'Handled Everything';
    public const TYPE = 'about:everything';

    public function supports(\Throwable $throwable): bool
    {
        return true;
    }

    public function handle(\Throwable $throwable): ApiProblem
    {
        return new ApiProblem(self::TITLE, self::TYPE);
    }
}
