<?php

namespace Modules\ProjectManagment\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\ProjectManagment\Http\Requests\FilterProjectRequest;
use Modules\ProjectManagment\Http\Requests\StoreProjectRequest;
use Modules\ProjectManagment\Http\Requests\UpdateProjectRequest;
use Modules\ProjectManagment\Http\Resources\ProjectResource;
use Modules\ProjectManagment\Services\ProjectService;

class ProjectController extends Controller
{
    public function __construct(private ProjectService $projectService) {}

    public function index(FilterProjectRequest $request): JsonResponse
    {
        $projects = $this->projectService->filter($request->validated());

        return response()->json([
            'data' => ProjectResource::collection($projects),
        ]);
    }

    public function store(StoreProjectRequest $request): JsonResponse
    {
        $project = $this->projectService->create($request->validated());

        return response()->json([
            'message' => 'Project created successfully.',
            'data' => new ProjectResource($project),
        ], 201);
    }

    public function show(int $project): JsonResponse
    {
        return response()->json([
            'data' => new ProjectResource($this->projectService->find($project)),
        ]);
    }

    public function update(UpdateProjectRequest $request, int $project): JsonResponse
    {
        $updated = $this->projectService->update($project, $request->validated());

        return response()->json([
            'message' => 'Project updated successfully.',
            'data' => new ProjectResource($updated),
        ]);
    }

    public function destroy(int $project): JsonResponse
    {
        $this->projectService->delete($project);

        return response()->json([
            'message' => 'Project deleted successfully.',
        ]);
    }
}
