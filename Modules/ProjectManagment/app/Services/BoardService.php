<?php

namespace Modules\ProjectManagment\Services;

use Illuminate\Support\Facades\Cache;
use Modules\ProjectManagment\Http\Resources\FormResource;
use Modules\ProjectManagment\Http\Resources\LeadResource;
use Modules\ProjectManagment\Http\Resources\ProjectResource;
use Modules\ProjectManagment\Models\Project;
use Modules\ProjectManagment\Models\Stage;
use Modules\ProjectManagment\Repositories\FormRepository;
use Modules\ProjectManagment\Repositories\LeadRepository;
use Modules\ProjectManagment\Repositories\ProjectRepository;
use Modules\ProjectManagment\Repositories\StageRepository;

class BoardService
{
    private const STAGES_LIMIT = 7;

    private const LEADS_PER_STAGE = 30;

    private const SKELETON_TTL = 600;

    public function __construct(
        private ProjectRepository $projectRepository,
        private FormRepository $formRepository,
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
        $project = $this->projectRepository->find($projectId);

        $skeleton = Cache::remember("board_skeleton:{$project->id}", self::SKELETON_TTL, fn () => $this->buildSkeleton($project));

        $stageIds = array_column($skeleton['stages'], 'id');
        $leads = $this->leadRepository->boardLeads($stageIds, self::LEADS_PER_STAGE)->groupBy('stage_id');
        $counts = $this->leadRepository->countsByStage($stageIds);

        $stages = array_map(fn (array $stage) => [
            ...$stage,
            'has_more' => ($counts[$stage['id']] ?? 0) > self::LEADS_PER_STAGE,
            'leads' => LeadResource::collection($leads->get($stage['id'], collect())),
        ], $skeleton['stages']);

        return [
            'project' => $skeleton['project'],
            'form' => $skeleton['form'],
            'has_more_stages' => $skeleton['has_more_stages'],
            'stages' => $stages,
        ];
    }

    private function buildSkeleton(Project $project): array
    {
        $form = $this->formRepository->firstOrCreateForProject($project->id)->load('fields.options');
        $stages = $this->stageRepository->forBoard($project->id, self::STAGES_LIMIT);

        return [
            'project' => (new ProjectResource($project))->resolve(),
            'form' => (new FormResource($form))->resolve(),
            'stages' => $stages->map(fn (Stage $stage) => [
                'id' => $stage->id,
                'name' => $stage->name,
                'sort_order' => $stage->sort_order,
            ])->all(),
            'has_more_stages' => $this->stageRepository->countForProject($project->id) > self::STAGES_LIMIT,
        ];
    }
}
