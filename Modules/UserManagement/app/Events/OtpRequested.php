<?php

namespace Modules\UserManagement\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Modules\UserManagement\Enums\OtpUsage;

class OtpRequested
{
    use Dispatchable;

    public function __construct(
        public string $email,
        public OtpUsage $usage,
    ) {}
}
