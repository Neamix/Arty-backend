<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MakeModuleFeature extends Command
{
    protected $signature = 'module:make-feature
        {module : The module name (e.g. ProjectManagment)}
        {name : The feature/entity name (e.g. Project)}
        {--force : Overwrite existing files}';

    protected $description = 'Generate an empty Controller, Service and Repository (DI-wired) inside a module';

    public function handle(): int
    {
        $module = Str::studly($this->argument('module'));
        $name = Str::studly($this->argument('name'));

        $modulePath = base_path("Modules/{$module}");

        if (! is_dir($modulePath)) {
            $this->components->error("Module [{$module}] not found at Modules/{$module}.");

            return self::FAILURE;
        }

        $files = [
            'Repository' => [
                'path' => "{$modulePath}/app/Repositories/{$name}Repository.php",
                'content' => $this->repositoryStub($module, $name),
            ],
            'Service' => [
                'path' => "{$modulePath}/app/Services/{$name}Service.php",
                'content' => $this->serviceStub($module, $name),
            ],
            'Controller' => [
                'path' => "{$modulePath}/app/Http/Controllers/{$name}Controller.php",
                'content' => $this->controllerStub($module, $name),
            ],
        ];

        foreach ($files as $type => $file) {
            if (file_exists($file['path']) && ! $this->option('force')) {
                $this->components->warn("{$type} already exists, skipped: ".$this->relative($file['path']));

                continue;
            }

            $this->ensureDirectory(dirname($file['path']));
            file_put_contents($file['path'], $file['content']);
            $this->components->info("{$type} created: ".$this->relative($file['path']));
        }

        return self::SUCCESS;
    }

    private function ensureDirectory(string $dir): void
    {
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }

    private function relative(string $path): string
    {
        return str_replace(base_path().DIRECTORY_SEPARATOR, '', $path);
    }

    private function controllerStub(string $module, string $name): string
    {
        return <<<PHP
        <?php

        namespace Modules\\{$module}\\Http\\Controllers;

        use App\\Http\\Controllers\\Controller;
        use Modules\\{$module}\\Services\\{$name}Service;

        class {$name}Controller extends Controller
        {
            public function __construct(private {$name}Service \${$this->camel($name)}Service) {}
        }

        PHP;
    }

    private function serviceStub(string $module, string $name): string
    {
        return <<<PHP
        <?php

        namespace Modules\\{$module}\\Services;

        use Modules\\{$module}\\Repositories\\{$name}Repository;

        class {$name}Service
        {
            public function __construct(private {$name}Repository \${$this->camel($name)}Repository) {}
        }

        PHP;
    }

    private function repositoryStub(string $module, string $name): string
    {
        return <<<PHP
        <?php

        namespace Modules\\{$module}\\Repositories;

        class {$name}Repository
        {
            //
        }

        PHP;
    }

    private function camel(string $name): string
    {
        return Str::camel($name);
    }
}
