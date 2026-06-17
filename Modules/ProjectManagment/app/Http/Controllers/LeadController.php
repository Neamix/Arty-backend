<?php

namespace Modules\ProjectManagment\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\ProjectManagment\Http\Requests\DestroyLeadRequest;
use Modules\ProjectManagment\Http\Requests\FilterLeadRequest;
use Modules\ProjectManagment\Http\Requests\ShowLeadRequest;
use Modules\ProjectManagment\Http\Requests\StoreLeadRequest;
use Modules\ProjectManagment\Http\Requests\UpdateLeadRequest;
use Modules\ProjectManagment\Http\Resources\LeadResource;
use Modules\ProjectManagment\Services\LeadService;

class LeadController extends Controller
{
    public function __construct(private LeadService $leadService) {}

    public function index(FilterLeadRequest $request): JsonResponse
    {
        $leads = $this->leadService->filter($request->validated());

        return response()->json([
            'data' => LeadResource::collection($leads),
        ]);
    }

    public function store(StoreLeadRequest $request): JsonResponse
    {
        $lead = $this->leadService->create($request->validated());

        return response()->json([
            'message' => 'Lead created successfully.',
            'data' => new LeadResource($lead),
        ], 201);
    }

    public function show(ShowLeadRequest $request): JsonResponse
    {
        return response()->json([
            'data' => new LeadResource($this->leadService->find($request->validated())),
        ]);
    }

    public function update(UpdateLeadRequest $request): JsonResponse
    {
        $updated = $this->leadService->update($request->validated());

        return response()->json([
            'message' => 'Lead updated successfully.',
            'data' => new LeadResource($updated),
        ]);
    }

    public function destroy(DestroyLeadRequest $request): JsonResponse
    {
        $this->leadService->delete($request->validated());

        return response()->json([
            'message' => 'Lead deleted successfully.',
        ]);
    }
}
