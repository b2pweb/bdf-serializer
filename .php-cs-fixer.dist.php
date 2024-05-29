<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__.'/src')
    ->exclude('var')
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR12' => true,
        //'@Symfony' => true,
        'array_syntax' => ['syntax' => 'short'],
        'declare_strict_types' => false,
        'constant_case' => false, // do not detect correctly NormalizationContext::NULL, so we need to disable it
    ])
    ->setFinder($finder)
    ->setCacheFile('.php-cs-fixer.cache') // forward compatibility with 3.x line
;
