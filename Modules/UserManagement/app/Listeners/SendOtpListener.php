<?php

namespace Modules\UserManagement\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Modules\UserManagement\Events\OtpRequested;
use Modules\UserManagement\Services\OtpService;

class SendOtpListener implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    public function __construct(private OtpService $otpService) {}

    public function handle(OtpRequested $event): void
    {
        $this->otpService->sendOtp($event->email, $event->usage);
    }
}
