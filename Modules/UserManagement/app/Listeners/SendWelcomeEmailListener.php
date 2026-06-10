<?php

namespace Modules\UserManagement\Listeners;

use App\Services\MailService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Modules\UserManagement\Events\UserRegistered;
use Modules\UserManagement\Mail\WelcomeMail;

class SendWelcomeEmailListener implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    public function __construct(private MailService $mailService) {}

    public function handle(UserRegistered $event): void
    {
        $this->mailService->send($event->user->email, new WelcomeMail($event->user->name));
    }
}
