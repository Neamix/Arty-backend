<?php

namespace Modules\ActivityLog\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\ActivityLog\Http\Requests\FilterActivityLogRequest;
use Modules\ActivityLog\Http\Resources\ActivityLogResource;
use Modules\ActivityLog\Services\ActivityLogger;

class ActivityLogController extends Controller
{
    public function __construct(private ActivityLogger $activityLogger) {}

    public function index(FilterActivityLogRequest $request): JsonResponse
    {
        $logs = $this->activityLogger->filter([
            ...$request->validated(),
            'workspace_id' => $request->user()->workspace_id,
        ]);

        return ActivityLogResource::collection($logs)->response();
    }
}
