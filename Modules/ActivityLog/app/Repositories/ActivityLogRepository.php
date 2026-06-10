<?php

namespace Modules\ActivityLog\Repositories;

use Illuminate\Contracts\Pagination\CursorPaginator;
use Modules\ActivityLog\Models\ActivityLog;

class ActivityLogRepository
{
    public function __construct(private ActivityLog $activityLog) {}

    public function create(array $attributes): ActivityLog
    {
        return $this->activityLog->newQuery()->create($attributes);
    }

    public function filter(array $filters, int $perPage = 30): CursorPaginator
    {
        return $this->activityLog->newQuery()
            ->filter($filters)
            ->with('causer')
            ->latest('id')
            ->cursorPaginate($perPage);
    }
}
