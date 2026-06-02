<?php

namespace Modules\ProjectManagement\Repositories;

use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Support\Facades\Date;
use Modules\ProjectManagement\Models\Project;
use Modules\ProjectManagement\Models\ProjectLead;
use Modules\ProjectManagement\Services\ProjectLeadService;

class ProjectLeadRepository
{
    public function __construct(private ProjectLead $lead) {}

    public function filter(int $projectId, array $filters, int $perPage = 30): CursorPaginator
    {
        return $this->lead->newQuery()
            ->where('project_id', $projectId)
            ->filter($filters)
            ->with('values')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->cursorPaginate($perPage);
    }

    public function paginateForStage(int $stageId, int $perPage = 30): CursorPaginator
    {
        return $this->lead->newQuery()
            ->where('project_stage_id', $stageId)
            ->with('values')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->cursorPaginate($perPage);
    }

    public function createForProject(Project $project, array $attributes): ProjectLead
    {
        return $project->leads()->create($attributes);
    }

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
            ->max('sort_order') + ProjectLeadService::SORT_GAP;
    }

    public function sortOrderOf(int $stageId, ?int $leadId): ?int
    {
        if ($leadId === null) {
            return null;
        }

        $value = $this->lead->newQuery()
            ->where('project_stage_id', $stageId)
            ->where('id', $leadId)
            ->value('sort_order');

        return $value === null ? null : (int) $value;
    }

    public function rebalanceStage(int $stageId): void
    {
        $leadIds = $this->lead->newQuery()
            ->where('project_stage_id', $stageId)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->lockForUpdate()
            ->pluck('id');

        foreach ($leadIds as $index => $id) {
            $this->lead->newQuery()
                ->whereKey($id)
                ->update(['sort_order' => ($index + 1) * ProjectLeadService::SORT_GAP]);
        }
    }

    public function syncValues(ProjectLead $lead, array $values): void
    {
        if ($values === []) {
            return;
        }

        $now = Date::now();

        $rows = array_map(fn (array $value): array => [
            'project_lead_id' => $lead->id,
            'project_form_field_id' => $value['field_id'],
            'value' => json_encode($value['value']),
            'created_at' => $now,
            'updated_at' => $now,
        ], array_values($values));

        $lead->values()->upsert(
            $rows,
            ['project_lead_id', 'project_form_field_id'],
            ['value', 'updated_at'],
        );
    }
}
