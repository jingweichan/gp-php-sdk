<?php
/**
 * Config file for PHP-CS-Fixer.
 *
 * Fixers based on a modification of StyleCI's Laravel preset:
 * https://styleci.readme.io/docs/presets
 */

$finder = PhpCsFixer\Finder::create()
    ->notPath('bootstrap/cache')
    ->notPath('storage')
    ->notPath('vendor')
    ->in(__DIR__)
    ->name('*.php')
    ->notName('*.blade.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

$rules = [
    '@Symfony' => true,
    '@PHP70Migration' => true,
    '@PHP71Migration' => true,
    'array_syntax' => ['syntax' => 'short'],
    'concat_space' => ['spacing' => 'one'],
    'not_operator_with_successor_space' => true,
    'ordered_class_elements' => true,
    'ordered_imports' => true,
    'phpdoc_align' => false,
    'phpdoc_no_alias_tag' => false,
    'phpdoc_summary' => false,
    'phpdoc_no_empty_return' => false,
    'trailing_comma_in_multiline_array' => true,
    'single_blank_line_at_eof' => false,
    'yoda_style' => false,
];

return PhpCsFixer\Config::create()
    ->setRules($rules)
    ->setFinder($finder)
    ->setUsingCache(false);
