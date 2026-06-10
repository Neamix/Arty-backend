<?php

namespace Modules\ProjectManagement\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\ProjectManagement\Http\Requests\ReorderStagesRequest;
use Modules\ProjectManagement\Http\Requests\StoreStageRequest;
use Modules\ProjectManagement\Http\Requests\UpdateStageRequest;
use Modules\ProjectManagement\Http\Resources\ProjectLeadResource;
use Modules\ProjectManagement\Http\Resources\ProjectStageResource;
use Modules\ProjectManagement\Models\Project;
use Modules\ProjectManagement\Models\ProjectStage;
use Modules\ProjectManagement\Services\ProjectLeadService;
use Modules\ProjectManagement\Services\ProjectStageService;

class ProjectStageController extends Controller
{
    public function __construct(
        private ProjectStageService $stageService,
        private ProjectLeadService $leadService,
    ) {}

    public function store(StoreStageRequest $request, Project $project): JsonResponse
    {
        $this->authorizeProject($request, $project);

        $stage = $this->stageService->create($project, $request->validated());

        return response()->json([
            'message' => 'Stage created successfully.',
            'data' => new ProjectStageResource($stage),
        ], 201);
    }

    public function update(UpdateStageRequest $request, Project $project, ProjectStage $stage): JsonResponse
    {
        $this->authorizeProject($request, $project);

        $stage = $this->stageService->update($stage, $request->validated());

        return response()->json([
            'message' => 'Stage updated successfully.',
            'data' => new ProjectStageResource($stage),
        ]);
    }

    public function destroy(Request $request, Project $project, ProjectStage $stage): JsonResponse
    {
        $this->authorizeProject($request, $project);

        $this->stageService->delete($stage);

        return response()->json([
            'message' => 'Stage deleted successfully.',
        ]);
    }

    public function reorder(ReorderStagesRequest $request, Project $project): JsonResponse
    {
        $this->authorizeProject($request, $project);

        $this->stageService->reorder($project, $request->validated()['stage_ids']);

        return response()->json([
            'message' => 'Stages reordered successfully.',
        ]);
    }

    public function leads(Request $request, Project $project, ProjectStage $stage): JsonResponse
    {
        $this->authorizeProject($request, $project);

        $leads = $this->leadService->leadsForStage($project, $stage);

        return ProjectLeadResource::collection($leads)->response();
    }

    private function authorizeProject(Request $request, Project $project): void
    {
        abort_unless($project->created_by === $request->user()->id, 403);
    }
}
