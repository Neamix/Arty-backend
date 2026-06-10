<?php

namespace Modules\ActivityLog\Services;

use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Database\Eloquent\Model;
use Modules\ActivityLog\Models\ActivityLog;
use Modules\ActivityLog\Repositories\ActivityLogRepository;

class ActivityLogger
{
    public function __construct(private ActivityLogRepository $activityLogRepository) {}

    /**
     * Record a single activity entry. This is the generic entry point other
     * modules call to log any create/update/move/delete event.
     *
     * @param  array<string, mixed>  $properties
     */
    public function log(
        string $event,
        ?Model $subject = null,
        ?Model $causer = null,
        array $properties = [],
        ?int $workspaceId = null,
        ?string $description = null,
    ): ActivityLog {
        return $this->activityLogRepository->create([
            'event' => $event,
            'description' => $description,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'causer_type' => $causer?->getMorphClass(),
            'causer_id' => $causer?->getKey(),
            'properties' => $properties === [] ? null : $properties,
            'workspace_id' => $workspaceId,
        ]);
    }

    public function filter(array $filters, int $perPage = 30): CursorPaginator
    {
        return $this->activityLogRepository->filter($filters, $perPage);
    }
}
