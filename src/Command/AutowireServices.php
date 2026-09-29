<?php

namespace Atto\Framework\Command;

use Atto\Framework\Attribute\Inject;
use Atto\Framework\Attribute\Service;
use Atto\Framework\Introspection\ModuleSource;
use Roave\BetterReflection\Reflection\ReflectionNamedType;
use Roave\BetterReflection\Reflection\ReflectionClass;
use Roave\BetterReflection\Reflection\ReflectionIntersectionType;
use Roave\BetterReflection\Reflection\ReflectionUnionType;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('atto:framework:autowire-services', description: 'Searches registered modules for any classes with a #[Service] attribute and generates a cache file containing autowiring for them')]
class AutowireServices extends Command
{
    public function __construct(
        private ModuleSource $moduleSource,
        private string $outputFile
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($this->outputFile === '') {
            $output->writeln('<error>No output file in configuration. Please set config.autowire.services</error>');
            return 1;
        }

        $commands = $this->getServices();

        $dirname = dirname($this->outputFile);
        if (!file_exists($dirname)) {
            mkdir($dirname, recursive: true);
        }

        file_put_contents($this->outputFile, "<?php \nreturn " . var_export($commands, true) . ';');

        return 0;
    }

    private function getServices(): array
    {
        $classes = [];

        foreach($this->moduleSource->getAllClasses() as $class) {
            try {
                $refl = current($class->getAttributesByName(Service::class));
                if ($refl) {
                    $attr = new Service(...$refl->getArguments());
                    $classes[$attr->name ?? '\\' . $class->getName()] = [
                        'args' => $this->buildArgs($class)
                    ];
                }
            } catch (\Exception $e) {}
        }

        return $classes;
    }

    /** @return string[] */
    private function buildArgs(ReflectionClass $class): array
    {
        $constructor = $class->getConstructor();
        if ($constructor === null || $constructor->getNumberOfParameters() === 0) {
            return [];
        }

        $args = [];

        foreach ($constructor->getParameters() as $param) {
            $refl = current($class->getAttributesByName(Inject::class));
            if ($refl) {
                $attr = new Inject(...$refl->getArguments());
                $args[] = $attr->service;
            } else {
                $type = $param->getType();
                switch (true) {
                    case $type instanceof ReflectionUnionType:
                    case $type instanceof ReflectionIntersectionType:
                    case $type instanceof ReflectionNamedType && $type->isBuiltin():
                    case $type === null:
                        throw new \RuntimeException(
                            sprintf('Cannot detect type for property %s, please add an #[Inject] attribute', $param->getName())
                        );
                }
                $args[] = '\\' . $type->getName();
            }
        }

        return $args;
    }
}
