<?php

namespace Modules\ProjectManagment\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Modules\ProjectManagment\Models\Lead;

class LeadRepository
{
    public function __construct(private Lead $lead) {}

    public function filter(array $filters): Collection
    {
        return $this->lead->filter($filters)
            ->with('answers.field')
            ->latest()
            ->get();
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

    /**
     * Flat list of leads for the given stages, capped at $perStage each (window function).
     *
     * @param  array<int, int>  $stageIds
     */
    public function boardLeads(array $stageIds, int $perStage): Collection
    {
        if (empty($stageIds)) {
            return new Collection;
        }

        $ranked = $this->lead
            ->select('*')
            ->selectRaw('ROW_NUMBER() OVER (PARTITION BY stage_id ORDER BY created_at DESC, id DESC) as rn')
            ->whereIn('stage_id', $stageIds);

        return $this->lead
            ->fromSub($ranked, 'leads')
            ->where('rn', '<=', $perStage)
            ->with('answers.field')
            ->orderBy('stage_id')
            ->get();
    }

    /**
     * @param  array<int, int>  $stageIds
     * @return array<int, int> map of stage_id => total lead count
     */
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

    /**
     * @param  array<int, string|null>  $answers  map of field_id => value
     */
    public function syncAnswers(Lead $lead, array $answers): void
    {
        foreach ($answers as $fieldId => $value) {
            $lead->answers()->updateOrCreate(['field_id' => $fieldId], ['value' => $value]);
        }

        $lead->load('answers.field');
    }
}
