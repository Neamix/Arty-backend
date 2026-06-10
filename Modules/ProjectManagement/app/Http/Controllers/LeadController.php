<?php

namespace Modules\ProjectManagement\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\ProjectManagement\Http\Requests\FilterLeadRequest;
use Modules\ProjectManagement\Http\Requests\MoveLeadRequest;
use Modules\ProjectManagement\Http\Requests\StoreLeadRequest;
use Modules\ProjectManagement\Http\Requests\UpdateLeadRequest;
use Modules\ProjectManagement\Http\Resources\LeadResource;
use Modules\ProjectManagement\Models\Lead;
use Modules\ProjectManagement\Models\Project;
use Modules\ProjectManagement\Services\LeadService;

class LeadController extends Controller
{
    public function __construct(private LeadService $leadService) {}

    public function index(FilterLeadRequest $request, Project $project): JsonResponse
    {
        $this->authorizeProject($request, $project);

        $leads = $this->leadService->filter($project, $request->validated());

        return LeadResource::collection($leads)->response();
    }

    public function store(StoreLeadRequest $request, Project $project): JsonResponse
    {
        $this->authorizeProject($request, $project);

        $lead = $this->leadService->create($project, $request->user(), $request->validated());

        return response()->json([
            'message' => 'Lead created successfully.',
            'data' => new LeadResource($lead),
        ], 201);
    }

    public function update(UpdateLeadRequest $request, Project $project, Lead $lead): JsonResponse
    {
        $this->authorizeProject($request, $project);

        $lead = $this->leadService->update($lead, $project, $request->user(), $request->validated());

        return response()->json([
            'message' => 'Lead updated successfully.',
            'data' => new LeadResource($lead),
        ]);
    }

    public function destroy(Request $request, Project $project, Lead $lead): JsonResponse
    {
        $this->authorizeProject($request, $project);

        $this->leadService->delete($lead, $project, $request->user());

        return response()->json([
            'message' => 'Lead deleted successfully.',
        ]);
    }

    public function move(MoveLeadRequest $request, Project $project, Lead $lead): JsonResponse
    {
        $this->authorizeProject($request, $project);

        $data = $request->validated();
        $lead = $this->leadService->move(
            $lead,
            $project,
            $request->user(),
            $data['stage_id'],
            $data['before_lead_id'] ?? null,
            $data['after_lead_id'] ?? null,
        );

        return response()->json([
            'message' => 'Lead moved successfully.',
            'data' => new LeadResource($lead),
        ]);
    }

    private function authorizeProject(Request $request, Project $project): void
    {
        abort_unless($project->created_by === $request->user()->id, 403);
    }
}
