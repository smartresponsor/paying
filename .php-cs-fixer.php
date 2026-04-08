<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()
    ->in([
        __DIR__ . '/src',
        __DIR__ . '/tests',
        __DIR__ . '/config',
    ])
    ->exclude([
        'var',
        'vendor',
        'node_modules',
        'public/build',
    ])
    ->notPath('reference.php')
    ->name('*.php');

$config = new Config();

$config
    ->setRiskyAllowed(true)
    ->setUsingCache(true)
    ->setCacheFile(__DIR__ . '/var/.php-cs-fixer.cache')
    ->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => true,

        // keep strict_types
        'declare_strict_types' => true,

        // ok to delete unused imports
        'no_unused_imports' => true,
        'ordered_imports' => [
            'imports_order' => ['class', 'function', 'const'],
            'sort_algorithm' => 'alpha',
        ],
        'single_import_per_statement' => true,

        // Preserve descriptive DocBlock narrative. Only type/signature-related
        // portions may be adjusted manually; the fixer must not rewrite or
        // downgrade semantic documentation into plain comments or strip tags.
        'comment_to_phpdoc' => false,
        'general_phpdoc_annotation_remove' => false,
        'general_phpdoc_tag_rename' => false,
        'no_superfluous_phpdoc_tags' => false,
        'phpdoc_align' => false,
        'phpdoc_line_span' => false,
        'phpdoc_order' => false,
        'phpdoc_order_by_value' => false,
        'phpdoc_separation' => false,
        'phpdoc_single_line_var_spacing' => false,
        'phpdoc_summary' => false,
        'phpdoc_to_comment' => false,
        'phpdoc_trim' => false,
        'phpdoc_trim_consecutive_blank_line_separation' => false,
        'phpdoc_types' => false,
        'phpdoc_types_order' => false,
        'phpdoc_var_annotation_correct_order' => false,
        'phpdoc_var_without_name' => false,

        // risky (category 1) — force OFF
        'no_unreachable_default_argument_value' => false,
        'logical_operators' => false,
        'error_suppression' => false,
        'set_type_to_cast' => false,
        'static_lambda' => false,
        'psr_autoloading' => false,
        'string_line_ending' => false,
        'non_printable_character' => false,
        'ereg_to_preg' => false,
        'modern_serialization_methods' => false,
        'native_function_invocation' => false,
        'native_constant_invocation' => false,
        'no_alias_functions' => false,
        'function_to_constant' => false,
        'no_php4_constructor' => false,
        'ordered_traits' => false,
        'no_homoglyph_names' => false,
        'single_line_comment_style' => false,
    ])
    ->setFinder($finder);

return $config;
