<?php

declare(strict_types=1);

namespace Atto\Framework\Module;

use League\Container\DefinitionContainerInterface;

/**
 * @phpstan-type ServiceConfig array{
 *     class?: mixed,
 *     factory?: mixed,
 *     args?: array<mixed>,
 *     tags?: list<string>,
 *     shared?: bool,
 * }
 */
final class ServiceLoader
{
    public function __construct(
        private DefinitionContainerInterface $container,
    ) {}

    public function loadFromConfig(array $serviceConfig): void
    {
        foreach ($serviceConfig as $service => $config) {
            if (isset($config['class'])) {
                $definition = $this->container->add($service, $config['class']);
            } elseif (isset($config['factory'])) {
                $definition = $this->container->add($service, $config['factory']);
            } else {
                $definition = $this->container->add($service);
            }

            if (isset($config['args'])) {
                $definition->addArguments($config['args']);
            }

            foreach ($config['tags'] ?? [] as $tag) {
                $definition->addTag($tag);
            }

            $definition->setShared($config['shared'] ?? true);
        }
    }
}
