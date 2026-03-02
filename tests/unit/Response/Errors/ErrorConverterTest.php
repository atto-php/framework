<?php

declare(strict_types=1);

namespace Atto\Framework\Tests\Unit\Response\Errors;

use Atto\Framework\Response\Errors\ErrorConverter;
use Atto\Framework\Response\Errors\ErrorHandler;
use Atto\Framework\Tests\Fixture\Response\Errors\HandlesEverything;
use Atto\Framework\Tests\Fixture\Response\Errors\HandlesNothing;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Psr\Http\Message\ResponseInterface;

#[\PHPUnit\Framework\Attributes\CoversClass(ErrorConverter::class)]
final class ErrorConverterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var array<ErrorHandler> $handlers
     */
    #[Test]
    #[DataProvider('provideErrorsandHandlers')]
    public function itLoadsServices(
        ResponseInterface $expected,
        array $handlers,
        bool $debug,
        \Throwable $error,
    ): void {
        $sut = new ErrorConverter(new Psr17Factory(), $handlers, $debug);

        $actual = $sut->convertFromThrowable($error);

        self::assertSame($expected->getStatusCode(), $actual->getStatusCode());
        self::assertSame($expected->getHeaders(), $actual->getHeaders());
        self::assertSame((string) $expected->getBody(), (string) $actual->getBody());
    }

    /**
     * @return \Generator<array{
     *     0: ResponseInterface, // response expected
     *     1: ErrorHandler[],    // handlers provided
     *     2: bool,              // debug flag
     *     3: \Throwable,        // error given
     * }>
     */
    public static function provideErrorsandHandlers(): \Generator
    {
        yield 'no handlers' => (function () {
            $expected = new \Nyholm\Psr7\Response(
                500,
                ['Content-Type' => 'application/problem+json'],
                json_encode([
                    'title' => 'Internal Server Error',
                    'type' => 'about:blank',
                    'status' => 500,
                    'detail' => 'An unhandled exception was raised',
                ]),
            );
            $error = new \RuntimeException('foo');

            return [
                $expected,
                [],
                false,
                $error,
            ];
        })();

        yield 'mismatched handler' => (function () {
            $expected = new \Nyholm\Psr7\Response(
                500,
                ['Content-Type' => 'application/problem+json'],
                json_encode([
                    'title' => 'Internal Server Error',
                    'type' => 'about:blank',
                    'status' => 500,
                    'detail' => 'An unhandled exception was raised',
                ]),
            );
            $error = new \RuntimeException('foo');

            return [
                $expected,
                [new HandlesNothing()],
                false,
                $error,
            ];
        })();

        yield 'matched handler' => (function () {
            $expected = new \Nyholm\Psr7\Response(
                500,
                ['Content-Type' => 'application/problem+json'],
                json_encode([
                    'title' => HandlesEverything::TITLE,
                    'type' => HandlesEverything::TYPE,
                ]),
            );

            $error = new \RuntimeException('foo');

            return [
                $expected,
                [new HandlesEverything()],
                false,
                $error,
            ];
        })();

        yield 'mismatched and matched handler' => (function () {
            $expected = new \Nyholm\Psr7\Response(
                500,
                ['Content-Type' => 'application/problem+json'],
                json_encode([
                    'title' => HandlesEverything::TITLE,
                    'type' => HandlesEverything::TYPE,
                ]),
            );

            $error = new \RuntimeException('foo');

            return [
                $expected,
                [new HandlesNothing(), new HandlesEverything()],
                false,
                $error,
            ];
        })();
    }
}
