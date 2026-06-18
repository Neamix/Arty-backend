<?php

namespace Modules\ProjectManagment\Repositories;

use Illuminate\Contracts\Pagination\CursorPaginator;
use Modules\ProjectManagment\Models\Lead;

class LeadRepository
{
    public function __construct(private Lead $lead) {}

    public function filter(array $filters, int $perPage = 30): CursorPaginator
    {
        return $this->lead->filter($filters)
            ->with('answers.field')
            ->orderByDesc('leads.created_at')
            ->orderByDesc('leads.id')
            ->cursorPaginate($perPage);
    }

    public function find(int $id): Lead
    {
        return $this->lead->with('answers.field')->findOrFail($id);
    }

    public function create(array $data): Lead
    {
        return $this->lead->create($data);
    }

    public function update(Lead $lead, array $data): Lead
    {
        $lead->update($data);

        return $lead->refresh();
    }

    public function delete(Lead $lead): void
    {
        $lead->delete();
    }
 
    public function countsByStage(array $stageIds): array
    {
        if (empty($stageIds)) {
            return [];
        }

        return $this->lead
            ->whereIn('stage_id', $stageIds)
            ->selectRaw('stage_id, COUNT(*) as aggregate')
            ->groupBy('stage_id')
            ->pluck('aggregate', 'stage_id')
            ->all();
    }

    public function syncAnswers(Lead $lead, array $answers): void
    {
        $rows = [];

        foreach ($answers as $fieldId => $value) {
            $rows[] = [
                'workspace_id' => $lead->workspace_id,
                'lead_id' => $lead->id,
                'field_id' => $fieldId,
                'value' => $value,
            ];
        }

        if ($rows !== []) {
            $lead->answers()->upsert($rows, ['lead_id', 'field_id'], ['value']);
        }

        $lead->load('answers.field');
    }
}
