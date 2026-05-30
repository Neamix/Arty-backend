<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$classes = [
    [\Modules\UserManagement\Http\Controllers\AuthController::class, 'register'],
    [\Modules\UserManagement\Services\AuthService::class, 'register'],
    [\Modules\UserManagement\Http\Requests\RegisterRequest::class, 'rules'],
    [\Modules\UserManagement\Repositories\UserRepository::class, 'create'],
    [\Modules\UserManagement\Services\WorkspaceService::class, 'createForOwner'],
];

foreach ($classes as [$class, $method]) {
    $r = new ReflectionMethod($class, $method);
    echo $class.'::'.$method.' start='.$r->getStartLine().' end='.$r->getEndLine().' file='.basename($r->getFileName()).PHP_EOL;

    // Try parse synthetic
    $parser = (new PhpParser\ParserFactory)->createForHostVersion();
    $methodDoc = $r->getDocComment() ?: '';
    $startLine = $r->getStartLine();
    $lines = str_repeat("\n", max($startLine - 3 - substr_count($methodDoc, "\n"), 1));
    $code = implode("\n", array_slice(
        preg_split('/\r\n|\r|\n/', file_get_contents($r->getFileName())),
        $startLine - 1,
        $r->getStartLine() === $r->getEndLine() ? 1 : max($r->getEndLine() - $r->getStartLine(), 1) + 1,
    ));
    $partialClass = "<?php{$lines} class ".class_basename($class)." {\n".$methodDoc."\n".$code."\n}";

    try {
        $parser->parse($partialClass);
        echo "  OK\n";
    } catch (PhpParser\Error $e) {
        echo "  FAIL: ".$e->getMessage().PHP_EOL;
        $errLines = explode("\n", $partialClass);
        $line = $e->getStartLine();
        echo "    line $line: ".($errLines[$line - 1] ?? '<EOF>').PHP_EOL;
    }
}
