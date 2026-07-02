<?php

namespace Modules\UserManagement\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\UserManagement\Events\OtpRequested;
use Modules\UserManagement\Events\WorkspaceInvitationCreated;
use Modules\UserManagement\Listeners\SendOtpListener;
use Modules\UserManagement\Listeners\SendWorkspaceInvitationListener;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array<string, array<int, string>>
     */
    protected $listen = [
        OtpRequested::class => [
            SendOtpListener::class,
        ],
        WorkspaceInvitationCreated::class => [
            SendWorkspaceInvitationListener::class,
        ],
    ];

    /**
     * Indicates if events should be discovered.
     *
     * @var bool
     */
    protected static $shouldDiscoverEvents = true;

    /**
     * Configure the proper event listeners for email verification.
     */
    protected function configureEmailVerification(): void {}
}
