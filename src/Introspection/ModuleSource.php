<?php

namespace Atto\Framework\Introspection;

use Roave\BetterReflection\BetterReflection;
use Roave\BetterReflection\Reflector\DefaultReflector;
use Roave\BetterReflection\SourceLocator\Type\DirectoriesSourceLocator;

class ModuleSource
{
    public function __construct(private array $modules)
    {

    }

    public function getAllClasses(): array
    {
        $astLocator = (new BetterReflection())->astLocator();
        $directoriesSourceLocator = new DirectoriesSourceLocator(
            array_map(fn ($module) => dirname(new \ReflectionClass($module)->getFileName()), $this->modules),
            $astLocator
        );
        $reflector = new DefaultReflector($directoriesSourceLocator);

        return $reflector->reflectAllClasses();
    }
}