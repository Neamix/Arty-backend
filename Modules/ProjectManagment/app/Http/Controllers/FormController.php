<?php

namespace Modules\ProjectManagment\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\ProjectManagment\Http\Requests\UpdateFormRequest;
use Modules\ProjectManagment\Http\Resources\FormResource;
use Modules\ProjectManagment\Services\FormService;

class FormController extends Controller
{
    public function __construct(private FormService $formService) {}

    public function show(int $project): JsonResponse
    {
        return response()->json([
            'data' => new FormResource($this->formService->find($project)),
        ]);
    }

    public function update(UpdateFormRequest $request, int $project): JsonResponse
    {
        $form = $this->formService->update($project, $request->validated());
        return response()->json([
            'message' => 'Form updated successfully.',
            'data' => new FormResource($form),
        ]);
    }
}
