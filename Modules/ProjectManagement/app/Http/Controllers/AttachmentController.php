<?php

namespace Modules\ProjectManagement\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\ProjectManagement\Http\Requests\FilterAttachmentRequest;
use Modules\ProjectManagement\Http\Requests\StoreAttachmentRequest;
use Modules\ProjectManagement\Http\Resources\AttachmentResource;
use Modules\ProjectManagement\Models\Attachment;
use Modules\ProjectManagement\Models\Lead;
use Modules\ProjectManagement\Models\Project;
use Modules\ProjectManagement\Services\AttachmentService;

class AttachmentController extends Controller
{
    public function __construct(private AttachmentService $attachmentService) {}

    public function index(FilterAttachmentRequest $request, Project $project, Lead $lead): JsonResponse
    {
        $this->authorizeProject($request, $project);

        $attachments = $this->attachmentService->filter([
            ...$request->validated(),
            'lead_id' => $lead->id,
        ]);

        return AttachmentResource::collection($attachments)->response();
    }

    public function store(StoreAttachmentRequest $request, Project $project, Lead $lead): JsonResponse
    {
        $this->authorizeProject($request, $project);

        $attachments = $this->attachmentService->upload($project, $lead, $request->user(), $request->file('attachments'));

        return response()->json([
            'message' => 'Attachments uploaded successfully.',
            'data' => AttachmentResource::collection($attachments),
        ], 201);
    }

    public function destroy(Request $request, Project $project, Lead $lead, Attachment $attachment): JsonResponse
    {
        $this->authorizeProject($request, $project);

        $this->attachmentService->delete($project, $lead, $attachment, $request->user());

        return response()->json([
            'message' => 'Attachment deleted successfully.',
        ]);
    }

    private function authorizeProject(Request $request, Project $project): void
    {
        abort_unless($project->created_by === $request->user()->id, 403);
    }
}
