<?php

declare(strict_types=1);

namespace Atto\Framework\Tests\Unit\Module;

use Atto\Framework\Module\ServiceLoader;
use Crell\ApiProblem\ApiProblem;
use DateTimeImmutable;
use League\Container\Container;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

/**
 * @phpstan-import-type ServiceConfig from ServiceLoader
 */
#[\PHPUnit\Framework\Attributes\CoversClass(ServiceLoader::class)]
final class ServiceLoaderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @param object $expected
     * @param class-string $service
     * @param ServiceConfig $config
     */
    #[Test]
    #[DataProvider('provideServiceConfigs')]
    public function itLoadsServices(
        object $expected,
        string $service,
        array $config,
    ): void {
        $container = new Container();

        $sut = new ServiceLoader($container);
        $sut->loadFromConfig($config);

        self::assertEquals($expected, $container->get($service));
    }

    /**
     * @return \Generator<array{
     *     0: object,
     *     1: class-string,
     *     2: ServiceConfig
     * }>
     */
    public static function provideServiceConfigs(): \Generator
    {
        yield 'requires no config' => (function () {
            $foo = new class {};

            return [$foo, $foo::class, [
                $foo::class => [],
            ]];
        })();

        yield 'requires integer' => (function () {
            $foo = new class (5) {
                public function __construct(private int $arg) {}
            };

            return [$foo, $foo::class, [
                $foo::class => ['args' => [5]],
            ]];
        })();

        yield 'requires another service' => (function () {
            $bar = new class {};
            $foo = new class ($bar) {
                public function __construct(private object $arg) {}
            };

            return [$foo, $foo::class, [
                $foo::class => ['args' => [$bar::class]],
                $bar::class => [],
            ]];
        })();

        yield 'requires an interface' => (function () {
            $bar = new class implements \Stringable {
                public function __toString(): string{return 'bar';}
            };

            $foo = new class ($bar) {
                public function __construct(private \Stringable $arg) {}
            };

            return [$foo, $foo::class, [
                $foo::class => ['args' => [\Stringable::class]],
                $bar::class => [],
                \Stringable::class => ['class' => $bar::class],
            ]];
        })();

        yield 'requires a tagged service' => (function () {
            $bar = new class {};

            $foo = new class ([$bar]) {
                public function __construct(private array $services) {}
            };

            return [$foo, $foo::class, [
                $foo::class => ['args' => ['bar']],
                $bar::class => ['tags' => ['bar']],
            ]];
        })();

        yield 'requires tagged services' => (function () {
            $bar = new class {};
            $baz = new class {};

            $foo = new class ([$bar, $baz]) {
                public function __construct(private array $services) {}
            };

            return [$foo, $foo::class, [
                $foo::class => ['args' => ['bar-baz']],
                $bar::class => ['tags' => ['bar-baz']],
                $baz::class => ['tags' => ['bar-baz']],
            ]];
        })();
    }
}
