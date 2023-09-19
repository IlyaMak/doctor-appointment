<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude('var')
;

return (new PhpCsFixer\Config())
     ->setRules([
         '@PSR2' => true,
         '@PSR12' => true,
         'array_syntax' => [
             'syntax' => 'short',
         ],
         'no_unused_imports' => true,
         'multiline_whitespace_before_semicolons' => [
             'strategy' => 'new_line_for_chained_calls'
         ],
         'global_namespace_import' => ['import_classes' => true],
         'cast_spaces' => true,
     ])
     ->setFinder($finder)
 ;
