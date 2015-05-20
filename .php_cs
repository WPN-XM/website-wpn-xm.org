<?php

/**
 * php-cs-fixer - configuration file
 */

use Symfony\CS\FixerInterface;

$finder = Symfony\CS\Finder\DefaultFinder::create()
    ->ignoreVCS(true)
    ->notName('.php_cs')
    ->notName('php-cs-fixer.report.txt')
    ->notName('AllTests.php')
    ->notName('composer.*')
    ->notName('*.phar')
    ->notName('*.ico')
    ->notName('*.ttf')
    ->notName('*.gif')
    ->notName('*.swf')
    ->notName('*.jpg')
    ->notName('*.png')
    ->notName('*.exe')
    ->exclude('vendor')
    ->exclide('registry')  // exclude the registry files
    ->exclude('nbproject') // netbeans project files
    ->in(__DIR__);

return Symfony\CS\Config\Config::create()
    ->finder($finder)
    // use SYMFONY_LEVEL:
    ->level(Symfony\CS\FixerInterface::SYMFONY_LEVEL)
    // and extra fixers:
    ->fixers(array(
        'align_equals',
        'align_double_arrow',
        'concat_with_spaces',
        'ordered_use',
        'strict',
        'strict_param',
        'short_array_syntax',
        '-phpdoc_short_description', // no dot at the end of a short desc
        '-return'                    // no empty line before @return
    ));