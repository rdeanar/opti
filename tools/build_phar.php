#!/usr/bin/env php
<?php

use Symfony\Component\Finder\Finder;

error_reporting(-1);
if (function_exists('ini_set')) {
    @ini_set('display_errors', 1);
    @ini_set('phar.readonly', false);
}

include __DIR__ . '/../vendor/autoload.php';

$pharFile = 'opti.phar';
$basePath = realpath(__DIR__ . '/../') . '/';
$basePathLength = strlen($basePath);

@unlink($pharFile);

$phar = new \Phar($pharFile, 0, 'opti.phar');
$phar->setSignatureAlgorithm(\Phar::SHA1);
$phar->startBuffering();


$finder_array = [];

$finder = new Finder();
$finder->files()
    ->ignoreVCS(true)
    ->in(__DIR__ . '/../src');


array_push($finder_array, $finder);

$finder = new Finder();
$finder->files()
    ->ignoreVCS(true)
    ->name('*.php')
    ->name('LICENSE')
    ->exclude('Tests')
    ->exclude('tests')
    ->exclude('docs')
    ->in(__DIR__ . '/../vendor');

array_push($finder_array, $finder);


foreach ($finder_array as $finder) {
    foreach ($finder as $file) {

        $relativePath = substr($file->getRealPath(), $basePathLength);

        echo 'Add ' . $relativePath . PHP_EOL;

        $phar->addFile($file->getRealPath(), $relativePath);
    }
}

echo 'Add bin/opti' . PHP_EOL;

$content = file_get_contents(__DIR__ . '/../bin/opti');
$content = preg_replace('{^#!/usr/bin/env php\s*}', '', $content);
$phar->addFromString('bin/opti', $content);


$stub = <<<'EOF'
#!/usr/bin/env php
<?php
 
error_reporting(-1);
if (function_exists('ini_set')) {
    @ini_set('display_errors', 1);
}

Phar::mapPhar('opti.phar');

require 'phar://opti.phar/bin/opti';

 __HALT_COMPILER();

EOF;

echo 'Set stub' . PHP_EOL;

$phar->setStub($stub);

echo 'Finalize PHAR' . PHP_EOL;

$phar->stopBuffering();

unset($phar);

echo 'Set PHAR executable' . PHP_EOL;

chmod($pharFile, 0755);