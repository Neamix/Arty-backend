<?php

namespace Modules\ProjectManagement\Repositories;

use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Support\Facades\Date;
use Modules\ProjectManagement\Models\Lead;
use Modules\ProjectManagement\Models\Project;
use Modules\ProjectManagement\Services\LeadService;

class LeadRepository
{
    public function __construct(private Lead $lead) {}

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
            ->where('stage_id', $stageId)
            ->with('values')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->cursorPaginate($perPage);
    }

    public function createForProject(Project $project, array $attributes): Lead
    {
        return $project->leads()->create($attributes);
    }

    public function update(Lead $lead, array $attributes): Lead
    {
        $lead->update($attributes);

        return $lead;
    }

    public function delete(Lead $lead): void
    {
        $lead->delete();
    }

    public function nextSortOrder(int $stageId): int
    {
        return (int) $this->lead->newQuery()
            ->where('stage_id', $stageId)
            ->max('sort_order') + LeadService::SORT_GAP;
    }

    public function sortOrderOf(int $stageId, ?int $leadId): ?int
    {
        if ($leadId === null) {
            return null;
        }

        $value = $this->lead->newQuery()
            ->where('stage_id', $stageId)
            ->where('id', $leadId)
            ->value('sort_order');

        return $value === null ? null : (int) $value;
    }

    public function rebalanceStage(int $stageId): void
    {
        $leadIds = $this->lead->newQuery()
            ->where('stage_id', $stageId)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->lockForUpdate()
            ->pluck('id');

        if ($leadIds->isEmpty()) {
            return;
        }

        foreach ($leadIds->chunk(500) as $chunk) {
            $cases = $chunk->map(
                fn (int $id, int $index): string => 'WHEN '.$id.' THEN '.(($index + 1) * LeadService::SORT_GAP),
            )->implode(' ');

            $this->lead->newQuery()
                ->whereIn('id', $chunk->values())
                ->update(['sort_order' => $this->lead->getConnection()->raw('CASE id '.$cases.' END')]);
        }
    }

    public function syncValues(Lead $lead, array $values): void
    {
        if ($values === []) {
            return;
        }

        $now = Date::now();

        $rows = array_map(fn (array $value): array => [
            'lead_id' => $lead->id,
            'project_form_field_id' => $value['field_id'],
            'value' => json_encode($value['value']),
            'created_at' => $now,
            'updated_at' => $now,
        ], array_values($values));

        $lead->values()->upsert(
            $rows,
            ['lead_id', 'project_form_field_id'],
            ['value', 'updated_at'],
        );
    }
}
