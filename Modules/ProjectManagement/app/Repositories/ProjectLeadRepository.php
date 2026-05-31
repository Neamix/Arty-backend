<?php

namespace Modules\ProjectManagement\Repositories;

use Modules\ProjectManagement\Models\Project;
use Modules\ProjectManagement\Models\ProjectLead;

class ProjectLeadRepository
{
    public function __construct(private ProjectLead $lead) {}

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function createForProject(Project $project, array $attributes): ProjectLead
    {
        return $project->leads()->create($attributes);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(ProjectLead $lead, array $attributes): ProjectLead
    {
        $lead->update($attributes);

        return $lead;
    }

    public function delete(ProjectLead $lead): void
    {
        $lead->delete();
    }

    public function nextSortOrder(int $stageId): int
    {
        return (int) $this->lead->newQuery()
            ->where('project_stage_id', $stageId)
            ->max('sort_order') + 1;
    }

    /**
     * Persist (upsert) a single dynamic field value for a lead.
     *
     * @param  mixed  $value
     */
    public function syncValue(ProjectLead $lead, int $fieldId, $value): void
    {
        $lead->values()->updateOrCreate(
            ['project_form_field_id' => $fieldId],
            ['value' => $value],
        );
    }
}
