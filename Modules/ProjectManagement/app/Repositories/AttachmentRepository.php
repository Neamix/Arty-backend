<?php

namespace Modules\ProjectManagement\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Modules\ProjectManagement\Models\Attachment;
use Modules\ProjectManagement\Models\Lead;

class AttachmentRepository
{
    public function __construct(private Attachment $attachment) {}

    public function filter(array $filters): Collection
    {
        return $this->attachment->newQuery()
            ->filter($filters)
            ->latest('id')
            ->get();
    }

    public function createForLead(Lead $lead, array $attributes): Attachment
    {
        return $lead->attachments()->create($attributes);
    }

    /**
     * @param  array<int, array{size: int, real_name: string, uploaded_name: string}>  $rows
     */
    public function createManyForLead(Lead $lead, array $rows): Collection
    {
        return $lead->attachments()->createMany($rows);
    }

    public function delete(Attachment $attachment): void
    {
        $attachment->delete();
    }
}
