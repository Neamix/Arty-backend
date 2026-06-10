<?php

namespace Modules\ProjectManagement\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\ActivityLog\Services\ActivityLogger;
use Modules\ProjectManagement\Models\Attachment;
use Modules\ProjectManagement\Models\Lead;
use Modules\ProjectManagement\Models\Project;
use Modules\ProjectManagement\Repositories\AttachmentRepository;

class AttachmentService
{
    public function __construct(
        private AttachmentRepository $attachmentRepository,
        private ActivityLogger $activityLogger,
    ) {}

    /**
     * @param  array<int, UploadedFile>  $files
     */
    public function upload(Project $project, Lead $lead, User $causer, array $files): Collection
    {
        $directory = "{$project->workspace_id}/{$project->id}/{$lead->id}";

        $rows = [];

        foreach ($files as $file) {
            $uploadedName = Str::random(64).'.'.$file->getClientOriginalExtension();

            $file->storeAs($directory, $uploadedName);

            $rows[] = [
                'size' => $file->getSize(),
                'real_name' => $file->getClientOriginalName(),
                'uploaded_name' => $uploadedName,
            ];
        }

        return DB::transaction(function () use ($project, $lead, $causer, $rows) {
            $created = $this->attachmentRepository->createManyForLead($lead, $rows);

            $this->activityLogger->log(
                event: 'attachment_uploaded',
                subject: $lead,
                causer: $causer,
                properties: ['names' => $created->pluck('real_name')->all()],
                workspaceId: $project->workspace_id,
            );

            return $created;
        });
    }

    public function filter(array $filters): Collection
    {
        return $this->attachmentRepository->filter($filters);
    }

    public function delete(Project $project, Lead $lead, Attachment $attachment, User $causer): void
    {
        $path = "{$project->workspace_id}/{$project->id}/{$lead->id}/{$attachment->uploaded_name}";

        DB::transaction(function () use ($project, $lead, $attachment, $causer) {
            $this->attachmentRepository->delete($attachment);

            $this->activityLogger->log(
                event: 'attachment_deleted',
                subject: $lead,
                causer: $causer,
                properties: ['name' => $attachment->real_name],
                workspaceId: $project->workspace_id,
            );
        });

        Storage::delete($path);
    }
}
