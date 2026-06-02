<?php

namespace Modules\ProjectManagement\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\ProjectManagement\Http\Requests\FilterLeadRequest;
use Modules\ProjectManagement\Http\Requests\MoveLeadRequest;
use Modules\ProjectManagement\Http\Requests\StoreLeadRequest;
use Modules\ProjectManagement\Http\Requests\UpdateLeadRequest;
use Modules\ProjectManagement\Http\Resources\ProjectLeadResource;
use Modules\ProjectManagement\Models\Project;
use Modules\ProjectManagement\Models\ProjectLead;
use Modules\ProjectManagement\Services\ProjectLeadService;

class ProjectLeadController extends Controller
{
    public function __construct(private ProjectLeadService $leadService) {}

    public function index(FilterLeadRequest $request, Project $project): JsonResponse
    {
        $this->authorizeProject($request, $project);

        $leads = $this->leadService->filter($project, $request->validated());

        return ProjectLeadResource::collection($leads)->response();
    }

    public function store(StoreLeadRequest $request, Project $project): JsonResponse
    {
        $this->authorizeProject($request, $project);

        $lead = $this->leadService->create($project, $request->user(), $request->validated());

        return response()->json([
            'message' => 'Lead created successfully.',
            'data' => new ProjectLeadResource($lead),
        ], 201);
    }

    public function update(UpdateLeadRequest $request, Project $project, ProjectLead $lead): JsonResponse
    {
        $this->authorizeProject($request, $project);

        $lead = $this->leadService->update($lead, $request->validated());

        return response()->json([
            'message' => 'Lead updated successfully.',
            'data' => new ProjectLeadResource($lead),
        ]);
    }

    public function destroy(Request $request, Project $project, ProjectLead $lead): JsonResponse
    {
        $this->authorizeProject($request, $project);

        $this->leadService->delete($lead);

        return response()->json([
            'message' => 'Lead deleted successfully.',
        ]);
    }

    public function move(MoveLeadRequest $request, Project $project, ProjectLead $lead): JsonResponse
    {
        $this->authorizeProject($request, $project);

        $data = $request->validated();
        $lead = $this->leadService->move(
            $lead,
            $data['project_stage_id'],
            $data['before_lead_id'] ?? null,
            $data['after_lead_id'] ?? null,
        );

        return response()->json([
            'message' => 'Lead moved successfully.',
            'data' => new ProjectLeadResource($lead),
        ]);
    }

    private function authorizeProject(Request $request, Project $project): void
    {
        abort_unless($project->created_by === $request->user()->id, 403);
    }
}
