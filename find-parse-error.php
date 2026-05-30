<?php

require __DIR__.'/vendor/autoload.php';

$parser = (new PhpParser\ParserFactory)->createForHostVersion();

$dirs = [
    __DIR__.'/app',
    __DIR__.'/Modules',
    __DIR__.'/config',
    __DIR__.'/bootstrap',
];

$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dirs[0]));
foreach ($dirs as $dir) {
    $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($rii as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            try {
                $parser->parse(file_get_contents($file->getPathname()));
            } catch (PhpParser\Error $e) {
                echo $file->getPathname().' :: '.$e->getMessage().PHP_EOL;
            }
        }
    }
}

echo "done\n";
