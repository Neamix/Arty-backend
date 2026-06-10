<?php

namespace Modules\ActivityLog\Services;

use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Database\Eloquent\Model;
use Modules\ActivityLog\Jobs\RecordActivityLog;
use Modules\ActivityLog\Repositories\ActivityLogRepository;

class ActivityLogger
{
    public function __construct(private ActivityLogRepository $activityLogRepository) {}

    /**
     * @param  array<string, mixed>  $properties
     */
    public function log(
        string $event,
        ?Model $subject = null,
        ?Model $causer = null,
        array $properties = [],
        ?int $workspaceId = null,
        ?string $description = null,
    ): void {
        RecordActivityLog::dispatch([
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
