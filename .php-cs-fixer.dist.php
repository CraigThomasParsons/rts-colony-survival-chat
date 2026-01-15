<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->ignoreVCS(true)
    ->exclude([
        'bootstrap/cache',
        'node_modules',
        'public',
        'storage',
        'vendor',
        'build',
    ])
    ->name('*.php')
    ->notName('*.blade.php');

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(false)
    ->setFinder($finder)
    ->setRules([
        '@PSR12' => true,
        'array_syntax' => ['syntax' => 'short'],
        'binary_operator_spaces' => ['default' => 'align_single_space_minimal'],
        'blank_line_before_statement' => ['statements' => ['return']],
        'braces' => [
            'allow_single_line_anonymous_class_with_empty_body' => false,
            'allow_single_line_closure' => false,
        ],
        'concat_space' => ['spacing' => 'one'],
        'explicit_string_variable' => true,
        'function_declaration' => ['closure_function_spacing' => 'one'],
        'fully_qualified_strict_types' => true,
        'lowercase_cast' => true,
        'method_argument_space' => ['on_multiline' => 'ensure_fully_multiline'],
        'native_function_invocation' => false,
        'no_extra_blank_lines' => ['tokens' => ['extra']],
        'no_short_bool_cast' => true,
        'no_singleline_whitespace_before_semicolons' => true,
        'not_operator_with_successor_space' => true,
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'phpdoc_align' => ['align' => 'vertical'],
        'phpdoc_separation' => true,
        'phpdoc_trim' => true,
        'single_import_per_statement' => true,
        'single_trait_insert_per_statement' => true,
        'ternary_operator_spaces' => true,
        'ternary_to_null_coalescing' => false,
        'yoda_style' => false,
    ]);
