<?php

namespace Modules\ProjectManagment\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\ProjectManagment\Http\Requests\FilterFieldRequest;
use Modules\ProjectManagment\Http\Requests\StoreFieldRequest;
use Modules\ProjectManagment\Http\Requests\UpdateFieldRequest;
use Modules\ProjectManagment\Http\Resources\FieldResource;
use Modules\ProjectManagment\Services\FieldService;

class FieldController extends Controller
{
    public function __construct(private FieldService $fieldService) {}

    public function index(FilterFieldRequest $request, int $project): JsonResponse
    {
        $fields = $this->fieldService->filter($project, $request->validated());

        return response()->json([
            'data' => FieldResource::collection($fields),
        ]);
    }

    public function store(StoreFieldRequest $request, int $project): JsonResponse
    {
        $field = $this->fieldService->create($project, $request->validated());

        return response()->json([
            'message' => 'Field created successfully.',
            'data' => new FieldResource($field),
        ], 201);
    }

    public function show(int $project, int $field): JsonResponse
    {
        return response()->json([
            'data' => new FieldResource($this->fieldService->find($project, $field)),
        ]);
    }

    public function update(UpdateFieldRequest $request, int $project, int $field): JsonResponse
    {
        $updated = $this->fieldService->update($project, $field, $request->validated());

        return response()->json([
            'message' => 'Field updated successfully.',
            'data' => new FieldResource($updated),
        ]);
    }

    public function destroy(int $project, int $field): JsonResponse
    {
        $this->fieldService->delete($project, $field);

        return response()->json([
            'message' => 'Field deleted successfully.',
        ]);
    }
}
