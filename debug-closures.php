<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Walk module + app, find every closure literal via php-parser AST, then attempt parse of '<?php <closure>;'
$dirs = [__DIR__.'/app', __DIR__.'/Modules'];
$parser = (new PhpParser\ParserFactory)->createForHostVersion();
$prettyPrinter = new PhpParser\PrettyPrinter\Standard;

foreach ($dirs as $dir) {
    $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($rii as $file) {
        if (! $file->isFile() || $file->getExtension() !== 'php') {
            continue;
        }
        $content = file_get_contents($file->getPathname());
        try {
            $stmts = $parser->parse($content);
        } catch (PhpParser\Error $e) {
            echo 'FILE PARSE FAIL: '.$file->getPathname().' :: '.$e->getMessage().PHP_EOL;
            continue;
        }
        $finder = new PhpParser\NodeFinder;
        $closures = $finder->find($stmts, fn ($n) => $n instanceof PhpParser\Node\Expr\Closure || $n instanceof PhpParser\Node\Expr\ArrowFunction);
        foreach ($closures as $closureNode) {
            $code = $prettyPrinter->prettyPrint([$closureNode]);
            $synth = '<?php '.$code.';';
            try {
                $parser->parse($synth);
            } catch (PhpParser\Error $e) {
                echo $file->getPathname().' closure at line '.$closureNode->getStartLine().' :: '.$e->getMessage().PHP_EOL;
            }
        }
    }
}
echo "done\n";
