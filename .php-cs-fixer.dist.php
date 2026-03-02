<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude(['generated']);

return (new PhpCsFixer\Config())
    ->setParallelConfig(PhpCsFixer\Runner\Parallel\ParallelConfigFactory::detect())
    ->setCacheFile(__DIR__ . '/.cache/php-cs-fixer')
    ->setFinder($finder)
    ->setRules([
        '@PER-CS3x0' => true,
    ]);
