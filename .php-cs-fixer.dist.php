<?php

declare(strict_types=1);

// Return a Code Sniffing configuration using
// all sniffers needed for PSR-2
// and additionally:
//  - Remove leading slashes in use clauses.
//  - PHP single-line arrays should not have trailing comma.
//  - Single-line whitespace before closing semicolon are prohibited.
//  - Remove unused use statements in the PHP source code
//  - Ensure Concatenation to have at least one whitespace around
//  - Remove trailing whitespace at the end of blank lines.
return (new \PhpCsFixer\Config())
    ->setFinder(
        (new PhpCsFixer\Finder())
            ->ignoreVCSIgnored(true)
            ->in(realpath(__DIR__))
    )
    ->setRiskyAllowed(true)
    ->setRules([
        '@DoctrineAnnotation' => true,
        '@PER-CS3.0' => true,
        '@PHP82Migration' => true,
        'cast_spaces' => ['space' => 'none'],
        'declare_parentheses' => true,
        'dir_constant' => true,
        'modernize_types_casting' => true,
        'native_function_casing' => true,
        'no_alias_functions' => true,
        'no_blank_lines_after_phpdoc' => true,
        'no_empty_phpdoc' => true,
        'no_empty_statement' => true,
        'no_extra_blank_lines' => true,
        'no_leading_namespace_whitespace' => true,
        'no_null_property_initialization' => true,
        'no_short_bool_cast' => true,
        'no_singleline_whitespace_before_semicolons' => true,
        'no_superfluous_elseif' => true,
        'no_trailing_comma_in_singleline' => true,
        'no_unneeded_control_parentheses' => true,
        'no_unused_imports' => true,
        'no_useless_else' => true,
        'no_useless_nullsafe_operator' => true,
        'ordered_imports' => ['imports_order' => ['class', 'function', 'const'], 'sort_algorithm' => 'alpha'],
        'php_unit_construct' => ['assertions' => ['assertEquals', 'assertSame', 'assertNotEquals', 'assertNotSame']],
        'php_unit_mock_short_will_return' => true,
        'php_unit_test_case_static_method_calls' => ['call_type' => 'self'],
        'phpdoc_no_empty_return' => true,
        'phpdoc_no_package' => true,
        'phpdoc_scalar' => true,
        'phpdoc_trim' => true,
        'phpdoc_types' => true,
        'phpdoc_types_order' => ['null_adjustment' => 'always_last', 'sort_algorithm' => 'none'],
        'single_line_comment_style' => ['comment_types' => ['hash']],
        'single_quote' => true,
        'single_space_around_construct' => true,
        'type_declaration_spaces' => true,
        'whitespace_after_comma_in_array' => ['ensure_single_space' => true],
        'yoda_style' => ['equal' => false, 'identical' => false, 'less_and_greater' => false],
    ]);
