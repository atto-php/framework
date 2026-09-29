<?php

declare(strict_types=1);

namespace Atto\Framework;

use Atto\Framework\Application\ConsoleApplication;
use Atto\Framework\Application\DefaultApplication;
use Atto\Framework\Command\AutowireCommands;
use Atto\Framework\Command\AutowireServices;
use Atto\Framework\Introspection\ModuleSource;
use Atto\Framework\Module\ModuleInterface;
use Atto\Framework\Response\Builder;
use Atto\Framework\Response\Errors\ApiProblemHandler;
use Atto\Framework\Response\Errors\ErrorConverter;
use Atto\Framework\Response\Errors\ErrorHandler;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Container\ContainerInterface;

final class Module implements ModuleInterface
{
    public function getServices(): array
    {
        return [
            DefaultApplication::class => [
                'args' => [
                    Builder::class
                ]
            ],
            ModuleSource::class => [
                'args' => [
                    'config.modules',
                ]
            ],
            ConsoleApplication::class => [
                'args' => [
                    ContainerInterface::class,
                    'config.commands',
                ]
            ],
            Builder::class => [
                'args' => [
                    ErrorConverter::class,
                    Psr17Factory::class
                ]
            ],
            ErrorConverter::class => [
                'args' => [
                    Psr17Factory::class,
                    ErrorHandler::class,
                    'debug',
                ]
            ],
            Psr17Factory::class => [],
            ApiProblemHandler::class => [
                'args' => [
                    'debug'
                ],
                'tags' => [
                    ErrorHandler::class
                ]
            ],
            AutowireCommands::class => [
                'args' => [
                    ModuleSource::class,
                    'config.autowire.commands'
                ]
            ],
            AutowireServices::class => [
                'args' => [
                    ModuleSource::class,
                    'config.autowire.services'
                ]
            ]
        ];
    }

    public function getConfig(): array
    {
        return [
            'commands' => [
                'atto:framework:autowire-commands' => AutowireCommands::class,
                'atto:framework:autowire-services' => AutowireServices::class,
            ]
        ];
    }
}