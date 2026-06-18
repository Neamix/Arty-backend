<?php

namespace Modules\ProjectManagment\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\ProjectManagment\Http\Requests\FieldOptionRequest;
use Modules\ProjectManagment\Http\Requests\FilterFieldOptionRequest;
use Modules\ProjectManagment\Http\Requests\StoreFieldOptionRequest;
use Modules\ProjectManagment\Http\Requests\UpdateFieldOptionRequest;
use Modules\ProjectManagment\Http\Resources\FieldOptionResource;
use Modules\ProjectManagment\Services\FieldOptionService;

class FieldOptionController extends Controller
{
    public function __construct(private FieldOptionService $fieldOptionService) {}

    public function index(FilterFieldOptionRequest $request, int $project, int $field): JsonResponse
    {
        $options = $this->fieldOptionService->filter($field, $request->validated());

        return response()->json([
            'data' => FieldOptionResource::collection($options),
        ]);
    }

    public function store(StoreFieldOptionRequest $request, int $project, int $field): JsonResponse
    {
        $option = $this->fieldOptionService->create($field, $request->validated());

        return response()->json([
            'message' => 'Option created successfully.',
            'data' => new FieldOptionResource($option),
        ], 201);
    }

    public function show(FieldOptionRequest $request, int $project, int $field, int $option): JsonResponse
    {
        return response()->json([
            'data' => new FieldOptionResource($this->fieldOptionService->find($option)),
        ]);
    }

    public function update(UpdateFieldOptionRequest $request, int $project, int $field, int $option): JsonResponse
    {
        $updated = $this->fieldOptionService->update($option, $request->validated());

        return response()->json([
            'message' => 'Option updated successfully.',
            'data' => new FieldOptionResource($updated),
        ]);
    }

    public function destroy(FieldOptionRequest $request, int $project, int $field, int $option): JsonResponse
    {
        $this->fieldOptionService->delete($option);

        return response()->json([
            'message' => 'Option deleted successfully.',
        ]);
    }
}
