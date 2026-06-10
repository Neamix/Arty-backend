<?php

namespace Modules\ActivityLog\Concerns;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Modules\ActivityLog\Models\ActivityLog;

trait LogsActivity
{
    public function activities(): MorphMany
    {
        return $this->morphMany(ActivityLog::class, 'subject')->latest('id');
    }
}
