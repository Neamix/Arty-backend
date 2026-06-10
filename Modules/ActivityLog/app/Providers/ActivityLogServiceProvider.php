<?php

namespace Modules\ActivityLog\Providers;

use Nwidart\Modules\Support\ModuleServiceProvider;

class ActivityLogServiceProvider extends ModuleServiceProvider
{
    protected string $name = 'ActivityLog';

    protected string $nameLower = 'activitylog';

    protected array $providers = [
        RouteServiceProvider::class,
    ];
}
