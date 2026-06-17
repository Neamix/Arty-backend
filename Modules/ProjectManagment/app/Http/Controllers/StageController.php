<?php

namespace Modules\ProjectManagment\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\ProjectManagment\Http\Requests\FilterStageRequest;
use Modules\ProjectManagment\Http\Requests\StoreStageRequest;
use Modules\ProjectManagment\Http\Requests\UpdateStageRequest;
use Modules\ProjectManagment\Http\Resources\StageResource;
use Modules\ProjectManagment\Services\StageService;

class StageController extends Controller
{
    public function __construct(private StageService $stageService) {}

    public function index(FilterStageRequest $request, int $project): JsonResponse
    {
        $stages = $this->stageService->filter($project, $request->validated());

        return response()->json([
            'data' => StageResource::collection($stages),
        ]);
    }

    public function store(StoreStageRequest $request, int $project): JsonResponse
    {
        $stage = $this->stageService->create($project, $request->validated());

        return response()->json([
            'message' => 'Stage created successfully.',
            'data' => new StageResource($stage),
        ], 201);
    }

    public function show(int $project, int $stage): JsonResponse
    {
        return response()->json([
            'data' => new StageResource($this->stageService->find($project, $stage)),
        ]);
    }

    public function update(UpdateStageRequest $request, int $project, int $stage): JsonResponse
    {
        $updated = $this->stageService->update($project, $stage, $request->validated());

        return response()->json([
            'message' => 'Stage updated successfully.',
            'data' => new StageResource($updated),
        ]);
    }

    public function destroy(int $project, int $stage): JsonResponse
    {
        $this->stageService->delete($project, $stage);

        return response()->json([
            'message' => 'Stage deleted successfully.',
        ]);
    }
}
