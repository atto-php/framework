<?php

namespace Atto\Framework\Application;

use Atto\Framework\Application\ApplicationInterface;
use Atto\Framework\Introspection\ModuleSource;
use Psr\Container\ContainerInterface;
use Roave\BetterReflection\BetterReflection;
use Roave\BetterReflection\Reflector\DefaultReflector;
use Roave\BetterReflection\SourceLocator\Type\DirectoriesSourceLocator;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\CommandLoader\ContainerCommandLoader;

class ConsoleApplication implements ApplicationInterface
{
    public function __construct(
        private ContainerInterface $container,
        private array $commands,
    ) {
    }

    public function run()
    {
        $commandLoader = new ContainerCommandLoader($this->container, $this->commands);

        $application = new Application();
        $application->setCommandLoader($commandLoader);

        return $application->run();
    }
}