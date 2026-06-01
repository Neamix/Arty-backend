<?php

namespace Modules\ProjectManagement\Providers;

use Nwidart\Modules\Support\ModuleServiceProvider;

class ProjectManagementServiceProvider extends ModuleServiceProvider
{
    protected string $name = 'ProjectManagement';

    protected string $nameLower = 'projectmanagement';

    protected array $providers = [
        RouteServiceProvider::class,
    ];
}
