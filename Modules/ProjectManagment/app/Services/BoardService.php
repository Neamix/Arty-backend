<?php

namespace Modules\ProjectManagment\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Modules\ProjectManagment\Http\Resources\LeadResource;
use Modules\ProjectManagment\Repositories\LeadRepository;
use Modules\ProjectManagment\Repositories\ProjectRepository;
use Modules\ProjectManagment\Repositories\StageRepository;

class BoardService
{
    public function __construct(
        private ProjectRepository $projectRepository,
        private StageRepository $stageRepository,
        private LeadRepository $leadRepository,
    ) {}

    public static function forgetSkeleton(?int $projectId): void
    {
        if ($projectId !== null) {
            Cache::forget("board_skeleton:{$projectId}");
        }
    }

    public function show(int $projectId): array
    {
        return $this->kanban($projectId);
    }

    public function kanban(int $projectId): array
    {
        $project = $this->projectRepository->find($projectId);
        $stagesLimit = 7;
        $leadsPerStage = 30;

        $projectStages = $this->stageRepository->forBoard(projectId: $project->id, limit: 7);
        $stageIds = $projectStages->pluck('id')->all();
        $stagesWithLeads = $this->stageRepository->boardLeads(stageIds: $stageIds, perStage: 30);
        $counts = $this->leadRepository->countsByStage(stageIds: $stageIds);

        return [
            'has_more_stages' => $this->stageRepository->countForProject(projectId: $project->id) > $stagesLimit,
            'stages' => $projectStages->map(fn ($stage) => [
                'id' => $stage->id,
                'name' => $stage->name,
                'sort_order' => $stage->sort_order,
                'has_more' => ($counts[$stage->id] ?? 0) > $leadsPerStage,
                'leads' => LeadResource::collection($stagesWithLeads->get($stage->id)?->leads ?? collect()),
            ])->values(),
        ];
    }

    public function sheet(int $projectId): array
    {
        $project = $this->projectRepository->find($projectId);
        $leads = $this->leadRepository->paginateForProject(projectId: $project->id, perPage: 40);

        return [
            'mode' => 'sheet',
            'leads' => $leads,
            'has_more' => $leads->hasMorePages(),
        ];
    }

    public function calendar(int $projectId, ?string $targetWeek): array
    {
        $project = $this->projectRepository->find($projectId);
        $weekStart = ($targetWeek === null ? now() : new Carbon($targetWeek))->startOfWeek();
        $weekEnd = $weekStart->copy()->endOfWeek();
        $leads = $this->leadRepository->paginateDueForProjectWeek(
            projectId: $project->id,
            weekStart: $weekStart,
            weekEnd: $weekEnd,
            perPage: 40,
        );

        return [
            'mode' => 'calendar',
            'week' => [
                'starts_at' => $weekStart->toDateString(),
                'ends_at' => $weekEnd->toDateString(),
            ],
            'leads' => $leads,
            'has_more' => $leads->hasMorePages(),
        ];
    }
}
