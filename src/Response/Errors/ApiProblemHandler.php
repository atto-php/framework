<?php

declare(strict_types=1);

namespace Atto\Framework\Response\Errors;

use Crell\ApiProblem\ApiProblem;

final class ApiProblemHandler implements ErrorHandler
{
    public function __construct(private readonly bool $debug = false) {}

    public function supports(\Throwable $throwable): bool
    {
        return $throwable instanceof HasApiProblem;
    }

    public function handle(\Throwable $throwable): ApiProblem
    {
        assert($throwable instanceof HasApiProblem);

        $title = $throwable->getTitle() ?? $throwable->getMessage() ?? '';

        $problem = new ApiProblem(
            $title,
            $throwable->getType() ?? 'about:blank'
        );

        $problem->setStatus($throwable->getStatusCode() ?? 500);

        if ($throwable->getDetail() !== null) {
            $problem->setDetail($throwable->getDetail());
        }

        $additionalFields = $throwable->getAdditionalInformation();
        if ($this->debug) {
            $additionalFields = array_merge($additionalFields, $throwable->getDebugInformation());
        }

        foreach ($additionalFields as $field => $value) {
            $problem[$field] = $value;
        }

        return $problem;
    }

}
