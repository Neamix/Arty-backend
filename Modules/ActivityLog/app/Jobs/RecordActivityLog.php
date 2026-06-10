<?php

namespace Modules\ActivityLog\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Modules\ActivityLog\Repositories\ActivityLogRepository;
use Throwable;

class RecordActivityLog implements ShouldQueue, ShouldQueueAfterCommit
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [1, 5, 10];

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function __construct(private array $attributes) {}

    public function handle(ActivityLogRepository $activityLogRepository): void
    {
        $activityLogRepository->create($this->attributes);
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('Failed to record activity log', [
            'attributes' => $this->attributes,
            'error' => $exception?->getMessage(),
        ]);
    }
}
