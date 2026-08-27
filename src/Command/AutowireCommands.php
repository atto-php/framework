<?php

namespace Atto\Framework\Command;

use Atto\Framework\Introspection\ModuleSource;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('atto:framework:autowire-commands', description: 'Searches registered modules for any command classes and generates a cache file containing them')]
class AutowireCommands extends Command
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
            $output->writeln('<error>No output file in configuration. Please set config.autowire.commands</error>');
            return 1;
        }

        $commands = $this->getCommands();

        $dirname = dirname($this->outputFile);
        if (!file_exists($dirname)) {
            mkdir($dirname, recursive: true);
        }

        file_put_contents($this->outputFile, "<?php \nreturn " . var_export($commands, true) . ';');

        return 0;
    }

    private function getCommands(): array
    {
        $classes = [];

        foreach($this->moduleSource->getAllClasses() as $class) {
            try {
                $refl = current($class->getAttributesByName(AsCommand::class));
                if ($refl) {
                    $attr = new AsCommand(...$refl->getAttributes());
                    $classes[$attr->name] = $class->getName();
                }
            } catch (\Exception $e) {}
        }

        return $classes;
    }
}
