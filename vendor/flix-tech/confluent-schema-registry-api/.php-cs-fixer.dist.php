<?php

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
    ->in('src')
    ->in('test');

return (new PhpCsFixer\Config())
    ->setRules(
        [
            'no_unused_imports' => true,
            'ordered_imports' => [
                'sort_algorithm' => 'alpha',
                'imports_order' => ['class', 'const', 'function'],
            ],
            'phpdoc_summary' => false,
            'phpdoc_to_comment' => false,
            'concat_space' => ['spacing' => 'one'],
            'array_syntax' => ['syntax' => 'short'],
        ]
    )
    ->setFinder($finder) ;
