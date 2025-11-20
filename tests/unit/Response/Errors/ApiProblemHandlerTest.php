<?php

declare(strict_types=1);

namespace Atto\Framework\Tests\Unit\Response\Errors;

use Atto\Framework\Response\Errors\ApiProblemHandler;
use Atto\Framework\Response\Errors\HasApiProblem;
use Crell\ApiProblem\ApiProblem;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

#[\PHPUnit\Framework\Attributes\CoversClass(ApiProblemHandler::class)]
final class ApiProblemHandlerTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    #[DataProvider('provideErrorsToSupport')]
    public function itSupportsErrorsWithApiProblems(
        bool $expected,
        \Throwable $error,
    ): void {
        $sutWithDebug = new ApiProblemHandler(true);
        $sutWithoutDebug = new ApiProblemHandler(false);

        self::assertSame($expected, $sutWithDebug->supports($error));
        self::assertSame($expected, $sutWithoutDebug->supports($error));
    }

    #[Test]
    #[DataProvider('provideErrorsToHandle')]
    public function itHandlesErrorsWithApiProblems(
        ApiProblem $expected,
        \Throwable $error,
        bool $debug,
    ): void {
        $sutWithDebug = new ApiProblemHandler($debug);

        self::assertEquals($expected, $sutWithDebug->handle($error));
    }

    /**
     * @return \Generator<list{
     *     bool,              // expected to support error given?
     *     \Throwable,        // error given
     * }>
     */
    public static function provideErrorsToSupport(): \Generator
    {
        yield 'runtime exception' => [false, new \RuntimeException()];
        yield 'implements HasApiProblem' => [true, self::getErrorThatHasApiProblem()];
    }

    /**
     * @return \Generator<list{
     *     ApiProblem, // ApiProblem expected
     *     \Throwable, // error given
     *     bool,       // debug flag
     * }>
     */
    public static function provideErrorsToHandle(): \Generator
    {
        yield 'debug disabled' => (function () {
            $error = self::getErrorThatHasApiProblem();

            $expected = (new ApiProblem($error->getTitle(), $error->getType()))
                ->setStatus($error->getStatusCode())
                ->setDetail($error->getDetail());

            return [$expected, $error, false];
        })();

        yield 'debug enabled' => (function () {
            $error = self::getErrorThatHasApiProblem();

            $expected = (new ApiProblem($error->getTitle(), $error->getType()))
                ->setStatus($error->getStatusCode())
                ->setDetail($error->getDetail());

            return [$expected, $error, true];
        })();
    }

    private static function getErrorThatHasApiProblem(): HasApiProblem
    {
        return new class extends \RuntimeException implements HasApiProblem {
            public function getStatusCode(): int {return 418;}
            public function getType(): ?string{return 'about:tests';}
            public function getTitle(): ?string{return 'TDD';}
            public function getDetail(): ?string{return 'Test driven development';}
            public function getAdditionalInformation(): array{return [];}
            public function getDebugInformation(): array{return [];}
        };
    }
}
