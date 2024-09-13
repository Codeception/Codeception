<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__ . '/src')
    ->in(__DIR__ . '/tests')->notPath('data/Invalid.php')
    ->in(__DIR__ . '/ext')
    ->append([__FILE__]);

$config = new PhpCsFixer\Config();
return $config->setRules([
    '@PSR12' => true,
    'array_syntax' => ['syntax' => 'short'],
    'braces' => ['allow_single_line_closure' => true,],
    'no_spaces_after_function_name' => true,
    'nullable_type_declaration_for_default_null_value' => true,
    'single_blank_line_at_eof' => true,
])->setFinder($finder);
